<?php

namespace App\Services\Warehouse;

use App\Http\Resources\Warehouse\ProductResource;
use App\Models\Product;
use App\Models\StockMovement;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WarehouseProductService
{
    use ApiResponseTrait;

    private const SORTABLE = ['name', 'price', 'stock_quantity', 'created_at', 'updated_at'];

    /** كل استعلام محصور بمنتجات المخزن المسجل دخول */
    private function scoped(): Builder
    {
        return Product::query()->forProvider(
            Product::PROVIDER_WAREHOUSE,
            Auth::id()
        );
    }

    private function findOrFail(int|string $id): ?Product
    {
        return $this->scoped()->find($id);
    }

    /** تسجيل حركة مخزون + تحديث الرصيد بمعاملة واحدة */
    private function recordMovement(Product $product, string $type, string $reason, float $quantity, ?string $notes = null): void
    {
        DB::transaction(function () use ($product, $type, $reason, $quantity, $notes) {
            $locked = Product::query()->whereKey($product->getKey())->lockForUpdate()->first();

            $newQuantity = match ($type) {
                StockMovement::TYPE_IN         => (float) $locked->stock_quantity + $quantity,
                StockMovement::TYPE_OUT        => (float) $locked->stock_quantity - $quantity,
                StockMovement::TYPE_ADJUSTMENT => $quantity,
            };

            $logged = $type === StockMovement::TYPE_ADJUSTMENT
                ? abs($newQuantity - (float) $locked->stock_quantity)
                : $quantity;

            StockMovement::create([
                'product_id'      => $locked->getKey(),
                'movement_type'   => $type,
                'reason'          => $reason,
                'quantity'        => round($logged, 2),
                'notes'           => $notes,
                'created_by_type' => Product::PROVIDER_WAREHOUSE,
                'created_by_id'   => Auth::id(),
                'created_at'      => now(),
            ]);

            $locked->update(['stock_quantity' => round($newQuantity, 2)]);
        });
    }

    // GET /warehouse/products
    public function index(Request $request)
    {
        $sortBy  = in_array($request->input('sort_by'), self::SORTABLE, true) ? $request->input('sort_by') : 'name';
        $sortDir = $request->input('sort_dir') === 'desc' ? 'desc' : 'asc';

        $products = $this->scoped()
            ->with('category')
            ->when($request->filled('q'), function (Builder $query) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(fn (Builder $w) => $w->where('name', 'like', $term)->orWhere('sku', 'like', $term));
            })
            ->when($request->filled('category_id'), fn (Builder $q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->boolean('low_stock'), fn (Builder $q) => $q->lowStock())
            ->when(! $request->boolean('with_archived'), fn (Builder $q) => $q->notArchived())
            ->orderBy($sortBy, $sortDir)
            ->paginate($request->integer('per_page', 15));

        return $this->sendResponse([
            'items'      => ProductResource::collection($products->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ], 'تم جلب المنتجات.');
    }

    // POST /warehouse/products
    public function store(Request $request, array $input)
    {
        if ($request->hasFile('main_image')) {
            $input['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        if ($request->hasFile('images')) {
            $input['images_json'] = collect($request->file('images'))
                ->map(fn ($file) => $file->store('products', 'public'))
                ->all();
        }

        $initialQuantity = (float) $input['stock_quantity'];
        $notes           = $input['notes'] ?? 'كمية أولية عند إضافة المنتج';
        unset($input['notes'], $input['images']);

        $product = DB::transaction(function () use ($input, $initialQuantity, $notes) {
            $product = Product::create(array_merge($input, [
                'provider_type'  => Product::PROVIDER_WAREHOUSE,
                'provider_id'    => Auth::id(),
                'stock_quantity' => 0, // بتنحط عن طريق حركة إدخال حتى يضل السجل صحيح
                'is_active'      => $input['is_active'] ?? true,
                'is_archived'    => false,
            ]));

            if ($initialQuantity > 0) {
                $this->recordMovement($product, StockMovement::TYPE_IN, StockMovement::REASON_PURCHASE, $initialQuantity, $notes);
            }

            return $product->refresh();
        });

        return $this->sendResponse(
            ProductResource::make($product->load('category'))->resolve(),
            'تمت إضافة المنتج.',
            201
        );
    }

    // PUT /warehouse/products/{id}
    public function update(Request $request, int|string $id, array $input)
    {
        $product = $this->findOrFail($id);

        if (! $product) {
            return $this->sendError('المنتج غير موجود أو لا يتبع مخزنك.', 404);
        }

        $newQuantity     = $input['stock_quantity'] ?? null;
        $adjustmentNotes = $input['adjustment_notes'] ?? 'تسوية كمية من قبل المخزن';
        unset($input['stock_quantity'], $input['adjustment_notes']);

        if ($request->hasFile('main_image')) {
            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            $input['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        if ($input !== []) {
            $product->update($input);
        }

        if ($newQuantity !== null && round((float) $newQuantity, 2) !== round((float) $product->stock_quantity, 2)) {
            $this->recordMovement($product, StockMovement::TYPE_ADJUSTMENT, StockMovement::REASON_STOCKTAKE, (float) $newQuantity, $adjustmentNotes);
        }

        return $this->sendResponse(
            ProductResource::make($product->refresh()->load('category'))->resolve(),
            'تم تعديل المنتج.'
        );
    }

    // DELETE /warehouse/products/{id} — أرشفة مش حذف
    public function destroy(int|string $id)
    {
        $product = $this->findOrFail($id);

        if (! $product) {
            return $this->sendError('المنتج غير موجود أو لا يتبع مخزنك.', 404);
        }

        // ما منحذف نهائياً لأن في طلبات وحركات مخزون مربوطة بالمنتج
        $product->update(['is_archived' => true, 'is_active' => false]);

        return $this->sendResponse(null, 'تمت أرشفة المنتج.');
    }
}
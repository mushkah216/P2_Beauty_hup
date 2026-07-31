<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Http\Requests\Expert\StoreInventoryItemRequest;
//use App\Http\Requests\Expert\StoreInventoryItemRequest as ExpertStoreInventoryItemRequest;
use App\Http\Requests\Expert\StoreStockMovementRequest;
use App\Http\Requests\Expert\UpdateInventoryItemRequest;
use App\Http\Resources\Expert\InventoryItemResource;
use App\Http\Resources\Expert\StockMovementResource;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\Expert\SmartMaterialAlertService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    use ApiResponseTrait;

    /** أعمدة مسموح الترتيب عليها (حتى ما يصير SQL injection من الـ query string) */
    private const SORTABLE = ['name', 'stock_quantity', 'created_at', 'updated_at', 'price'];

    public function __construct(private readonly SmartMaterialAlertService $smartAlert)
    {
    }

    /* =========================================================
     |  Helpers
     ========================================================= */

    /** كل استعلام محصور بمواد الخبير المسجل دخول — ما حد يوصل لمواد غيره */
    private function scoped(Request $request): Builder
    {
        return Product::query()->forProvider(
            Product::PROVIDER_EXPERT,
            $request->user()->getKey()
        );
    }

    /**
     * منرمي HttpResponseException حتى الخطأ يرجع بنفس فورمات sendError
     * بدل الفورمات الافتراضي تبع Laravel.
     */
    private function findItemOrFail(Request $request, int|string $id): Product
    {
        $item = $this->scoped($request)->find($id);

        if ($item === null) {
            throw new HttpResponseException(
                $this->sendError('المادة غير موجودة أو لا تتبع حسابك.', 404)
            );
        }

        return $item;
    }

    /** تغليف بيانات الصفحات */
    private function withPagination(LengthAwarePaginator $paginator, array $items): array
    {
        return [
            'items'      => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ];
    }

    
    private function recordMovement(
        Request $request,
        Product $item,
        string $type,
        string $reason,
        float $quantity,
        ?string $notes = null,
        ?string $referenceType = null,
        int|string|null $referenceId = null
    ): StockMovement {
        return DB::transaction(function () use ($request, $item, $type, $reason, $quantity, $notes, $referenceType, $referenceId) {

            // قفل الصف حتى ما يصير تعارض إذا انفتحت أكتر من عملية بنفس الوقت
            $locked = Product::query()->whereKey($item->getKey())->lockForUpdate()->first();

            $newQuantity = match ($type) {
                StockMovement::TYPE_IN         => (float) $locked->stock_quantity + $quantity,
                StockMovement::TYPE_OUT        => (float) $locked->stock_quantity - $quantity,
                StockMovement::TYPE_ADJUSTMENT => $quantity, // بالتسوية الكمية هي الرصيد الجديد
            };

            if ($newQuantity < 0) {
                // الاستثناء يرجّع المعاملة كلها (rollback) وبنفس الوقت يرد بفورماتك
                throw new HttpResponseException(
                    $this->sendError('الكمية المطلوبة أكبر من الموجود بالمخزون.', 422, [
                        'available_quantity' => (float) $locked->stock_quantity,
                    ])
                );
            }

            // بالتسوية منسجّل الفرق حتى يضل السجل مفهوم
            $loggedQuantity = $type === StockMovement::TYPE_ADJUSTMENT
                ? abs($newQuantity - (float) $locked->stock_quantity)
                : $quantity;

            $movement = StockMovement::create([
                'product_id'      => $locked->getKey(),
                'movement_type'   => $type,
                'reason'          => $reason,
                'quantity'        => round($loggedQuantity, 2),
                'reference_type'  => $referenceType,
                'reference_id'    => $referenceId,
                'notes'           => $notes,
                'created_by_type' => Product::PROVIDER_EXPERT,
                'created_by_id'   => $request->user()->getKey(),
                'created_at'      => now(),
            ]);

            $locked->update(['stock_quantity' => round($newQuantity, 2)]);

            $item->refresh();

            // الإشعار ثانوي — لو فشل ما بيصح يرجّع حركة المخزون كلها
            if ($item->is_low_stock) {
                try {
                    $this->pushLowStockNotification($request, $item);
                } catch (\Throwable $e) {
                    report($e); // بينكتب باللوج وبس، والعملية بتكمل
                }
            }

            return $movement;
        });
    
    }

    /** إشعار فوري بانخفاض المخزون (use case موجود بالـ SRS) */
    private function pushLowStockNotification(Request $request, Product $item): void
    {
        DB::table('notifications')->insert([
            'recipient_type' => Product::PROVIDER_EXPERT,
            'recipient_id'   => $request->user()->getKey(),
            'type'           => 'low_stock',
            'title'          => 'تنبيه نفاد مادة',
            'body'           => "الكمية المتوفرة من \"{$item->name}\" وصلت للحد الأدنى ({$item->stock_quantity}).",
            'data_json'      => json_encode([
                'product_id'     => $item->getKey(),
                'stock_quantity' => (float) $item->stock_quantity,
            ], JSON_UNESCAPED_UNICODE),
            'is_read'        => false,
            'created_at'     => now(),
        ]);
    }

    /* =========================================================
     |  Endpoints
     ========================================================= */

    /** GET expert/inventory */
    public function index(Request $request): JsonResponse
    {
        $sortBy  = in_array($request->input('sort_by'), self::SORTABLE, true)
            ? $request->input('sort_by')
            : 'name';
        $sortDir = $request->input('sort_dir') === 'desc' ? 'desc' : 'asc';

        $items = $this->scoped($request)
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

        return $this->sendResponse(
            $this->withPagination($items, InventoryItemResource::collection($items->getCollection())->resolve()),
            'تم جلب المخزون بنجاح.'
        );
    }

    /** POST expert/inventory — إضافة مادة مشتراة */
    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['main_image', 'images', 'notes']);

        if ($request->hasFile('main_image')) {
            $data['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        if ($request->hasFile('images')) {
            $data['images_json'] = collect($request->file('images'))
                ->map(fn ($file) => $file->store('products', 'public'))
                ->all();
        }

        $initialQuantity = (float) $data['stock_quantity'];

        $item = DB::transaction(function () use ($request, $data, $initialQuantity) {
            $item = Product::create(array_merge($data, [
                'provider_type'  => Product::PROVIDER_EXPERT,
                'provider_id'    => $request->user()->getKey(),
                'stock_quantity' => 0, // منخليها صفر وبعدين منزّل حركة إدخال حتى يضل السجل صحيح
                'is_active'      => $data['is_active'] ?? true,
                'is_archived'    => false,
            ]));

            if ($initialQuantity > 0) {
                $this->recordMovement(
                    $request,
                    $item,
                    StockMovement::TYPE_IN,
                    StockMovement::REASON_PURCHASE,
                    $initialQuantity,
                    $request->input('notes', 'كمية أولية عند إضافة المادة')
                );
            }

            return $item->refresh();
        });

        return $this->sendResponse(
            InventoryItemResource::make($item->load('category'))->resolve(),
            'تمت إضافة المادة للمخزون.',
            201
        );
    }

    /** GET expert/inventory/{item} */
    public function show(Request $request, int|string $item): JsonResponse
    {
        $product = $this->findItemOrFail($request, $item);

        $product->load([
            'category',
            'serviceMaterials.service',
            'movements' => fn ($q) => $q->latest('created_at')->limit(20),
        ]);

        // الكمية تكفي كم شخص
        $product->sufficient_for_persons = $this->smartAlert->sufficientForPersons($product);

        return $this->sendResponse(
            InventoryItemResource::make($product)->resolve(),
            'تم جلب تفاصيل المادة.'
        );
    }

    /** PUT|POST expert/inventory/{item} — تعديل البيانات و/أو تسوية الكمية */
    public function update(UpdateInventoryItemRequest $request, int|string $item): JsonResponse
    {
        $product = $this->findItemOrFail($request, $item);
        $data    = $request->safe()->except(['main_image', 'stock_quantity', 'adjustment_notes']);

        if ($request->hasFile('main_image')) {
            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            $data['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        if ($data !== []) {
            $product->update($data);
        }

        // تعديل الكمية = تسوية جرد، وتتسجل كحركة adjustment
        if ($request->has('stock_quantity')) {
            $newQuantity = (float) $request->input('stock_quantity');

            if (round($newQuantity, 2) !== round((float) $product->stock_quantity, 2)) {
                $this->recordMovement(
                    $request,
                    $product,
                    StockMovement::TYPE_ADJUSTMENT,
                    StockMovement::REASON_STOCKTAKE,
                    $newQuantity,
                    $request->input('adjustment_notes', 'تسوية كمية من قبل الخبير')
                );
            }
        }

        return $this->sendResponse(
            InventoryItemResource::make($product->refresh()->load('category'))->resolve(),
            'تم تعديل المادة.'
        );
    }

    /** DELETE expert/inventory/{item} — أرشفة، مش حذف نهائي */
    public function destroy(Request $request, int|string $item): JsonResponse
    {
        $product = $this->findItemOrFail($request, $item);

        // ما منحذف نهائياً لأن في حركات مخزون وتقارير مرتبطة بالمادة
        $product->update(['is_archived' => true, 'is_active' => false]);

        return $this->sendResponse(null, 'تمت أرشفة المادة.');
    }

    /** POST expert/inventory/{item}/restore */
    public function restore(Request $request, int|string $item): JsonResponse
    {
        $product = $this->findItemOrFail($request, $item);
        $product->update(['is_archived' => false, 'is_active' => true]);

        return $this->sendResponse(
            InventoryItemResource::make($product)->resolve(),
            'تم استرجاع المادة.'
        );
    }

    /** GET expert/inventory/{item}/movements — سجل الحركات */
    public function movements(Request $request, int|string $item): JsonResponse
    {
        $product = $this->findItemOrFail($request, $item);

        $movements = $product->movements()
            ->when($request->filled('movement_type'), fn ($q) => $q->where('movement_type', $request->input('movement_type')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->latest('created_at')
            ->paginate($request->integer('per_page', 20));

        return $this->sendResponse(
            $this->withPagination($movements, StockMovementResource::collection($movements->getCollection())->resolve()),
            'تم جلب سجل الحركات.'
        );
    }

    /** POST expert/inventory/{item}/movements — إدخال كمية / خصم استهلاك / تسوية */
    public function storeMovement(StoreStockMovementRequest $request, int|string $item): JsonResponse
    {
        $product = $this->findItemOrFail($request, $item);

        $movement = $this->recordMovement(
            $request,
            $product,
            $request->input('movement_type'),
            $request->input('reason'),
            (float) $request->input('quantity'),
            $request->input('notes'),
            $request->input('reference_type'),
            $request->input('reference_id')
        );

        return $this->sendResponse([
            'movement' => StockMovementResource::make($movement)->resolve(),
            'item'     => InventoryItemResource::make($product->refresh())->resolve(),
        ], 'تم تسجيل الحركة وتحديث الكمية.', 201);
    }

    /** GET expert/inventory/alerts — المواد يلي وصلت للحد الأدنى */
    public function alerts(Request $request): JsonResponse
    {
        $items = $this->scoped($request)
            ->notArchived()
            ->lowStock()
            ->orderBy('stock_quantity')
            ->get();

        return $this->sendResponse([
            'count' => $items->count(),
            'items' => InventoryItemResource::collection($items)->resolve(),
        ], 'تم جلب تنبيهات نفاد المواد.');
    }

    /**
     * GET expert/inventory/smart-alert
     * الإشعار الذكي بالمواد المطلوبة لحجوزات بكرا (أو أي تاريخ عبر ?date=YYYY-MM-DD)
     */
    public function smartAlert(Request $request): JsonResponse
    {
        $request->validate(['date' => ['nullable', 'date']]);

        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::tomorrow();

        $result = $this->smartAlert->forDate($request->user()->getKey(), $date);

        return $this->sendResponse($result, 'تم حساب المواد المطلوبة.');
}
}

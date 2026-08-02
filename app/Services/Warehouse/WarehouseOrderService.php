<?php

namespace App\Services\Warehouse;

use App\Http\Resources\Warehouse\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseOrderService
{
    use ApiResponseTrait;

    /**
     * جدول orders ما فيه ربط مباشر بالمخزن، فمنوصل للطلبات
     * عن طريق بنودها -> المنتجات -> المزود.
     */
    private function scoped(): Builder
    {
        return Order::query()->whereHas('items.product', function (Builder $q) {
            $q->where('provider_type', Product::PROVIDER_WAREHOUSE)
              ->where('provider_id', Auth::id());
        });
    }

    /** تحميل بنود هذا المخزن فقط، مش بنود الطلب كلها */
    private function withMyItems(Builder $query): Builder
    {
        return $query->with([
            'user',
            'items' => fn ($q) => $q->whereHas('product', function (Builder $p) {
                $p->where('provider_type', Product::PROVIDER_WAREHOUSE)
                  ->where('provider_id', Auth::id());
            })->with('product:id,name,sku'),
        ]);
    }

    // GET /warehouse/orders
    public function index(Request $request)
    {
        $orders = $this->withMyItems($this->scoped())
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from'), fn (Builder $q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn (Builder $q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->sendResponse([
            'items'      => OrderResource::collection($orders->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ], 'تم جلب الطلبات.');
    }

    // GET /warehouse/orders/{id}
    public function show(int|string $id)
    {
        $order = $this->withMyItems($this->scoped())->find($id);

        if (! $order) {
            return $this->sendError('الطلب غير موجود أو لا يخص مخزنك.', 404);
        }

        return $this->sendResponse(OrderResource::make($order)->resolve(), 'تم جلب تفاصيل الطلب.');
    }

    // PUT /warehouse/orders/{id}/accept
    public function accept(int|string $id)
    {
        $order = $this->withMyItems($this->scoped())->find($id);

        if (! $order) {
            return $this->sendError('الطلب غير موجود أو لا يخص مخزنك.', 404);
        }

        if ($order->status !== Order::STATUS_PENDING) {
            return $this->sendError('لا يمكن قبول طلب حالته: '.$order->status, 422);
        }

        // فحص توفر الكميات قبل أي تعديل
        foreach ($order->items as $item) {
            if ((float) $item->product->stock_quantity < (float) $item->quantity) {
                return $this->sendError(
                    'الكمية غير كافية للمنتج: '.$item->product->name,
                    422,
                    ['product_id' => $item->product_id, 'available' => (float) $item->product->stock_quantity]
                );
            }
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $locked = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();

                StockMovement::create([
                    'product_id'      => $locked->getKey(),
                    'movement_type'   => StockMovement::TYPE_OUT,
                    'reason'          => StockMovement::REASON_SALE,
                    'quantity'        => (float) $item->quantity,
                    'reference_type'  => 'order',
                    'reference_id'    => $order->getKey(),
                    'notes'           => 'خصم عند قبول الطلب رقم '.$order->getKey(),
                    'created_by_type' => Product::PROVIDER_WAREHOUSE,
                    'created_by_id'   => Auth::id(),
                    'created_at'      => now(),
                ]);

                $locked->update([
                    'stock_quantity' => round((float) $locked->stock_quantity - (float) $item->quantity, 2),
                ]);
            }

            $order->update(['status' => Order::STATUS_CONFIRMED]);
        });

        return $this->sendResponse(
            OrderResource::make($order->refresh()->load('user', 'items.product'))->resolve(),
            'تم قبول الطلب وخصم الكميات من المخزون.'
        );
    }

    // PUT /warehouse/orders/{id}/reject
    public function reject(int|string $id, array $input)
    {
        $order = $this->scoped()->find($id);

        if (! $order) {
            return $this->sendError('الطلب غير موجود أو لا يخص مخزنك.', 404);
        }

        if ($order->status !== Order::STATUS_PENDING) {
            return $this->sendError('لا يمكن رفض طلب حالته: '.$order->status, 422);
        }

        // ما في عمود مخصص لسبب الرفض، فمنضيفه على notes
        $order->update([
            'status' => Order::STATUS_CANCELLED,
            'notes'  => trim(($order->notes ? $order->notes."\n" : '').'سبب الرفض من المخزن: '.$input['reason']),
        ]);

        return $this->sendResponse(null, 'تم رفض الطلب.');
    }

    // PUT /warehouse/orders/{id}/status
    public function updateStatus(int|string $id, array $input)
    {
        $order = $this->scoped()->find($id);

        if (! $order) {
            return $this->sendError('الطلب غير موجود أو لا يخص مخزنك.', 404);
        }

        // التسلسل المنطقي: confirmed -> processing -> shipped -> delivered
        $allowed = [
            Order::STATUS_CONFIRMED  => [Order::STATUS_PROCESSING],
            Order::STATUS_PROCESSING => [Order::STATUS_SHIPPED],
            Order::STATUS_SHIPPED    => [Order::STATUS_DELIVERED],
        ];

        $next = $input['status'];

        if (! isset($allowed[$order->status]) || ! in_array($next, $allowed[$order->status], true)) {
            return $this->sendError('لا يمكن الانتقال من حالة '.$order->status.' إلى '.$next, 422);
        }

        $order->update(['status' => $next]);

        return $this->sendResponse(['status' => $order->status], 'تم تحديث حالة الطلب.');
    }
}
<?php

namespace App\Http\Resources\Warehouse;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'status'          => $this->status,

            // الجهة المشترية — use case "ربط الطلبات بالمستخدمين" بالـ SRS
            'buyer'           => $this->whenLoaded('user', fn () => [
                'id'        => $this->user?->id,
                'full_name' => $this->user?->full_name,
                'phone'     => $this->user?->phone,
                'email'     => $this->user?->email,
            ]),

            'address_id'      => $this->address_id,

            // إجماليات الطلب كامل (ممكن تشمل منتجات مخازن تانية)
            'order_totals'    => [
                'total_amount'    => $this->total_amount,
                'discount_amount' => $this->discount_amount,
                'shipping_amount' => $this->shipping_amount,
                'final_amount'    => $this->final_amount,
            ],

            // بنود هذا المخزن فقط + إجماليها
            'my_items'        => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id'           => $item->id,
                'product_id'   => $item->product_id,
                'product_name' => $item->product?->name,
                'sku'          => $item->product?->sku,
                'quantity'     => (int) $item->quantity,
                'unit_price'   => $item->unit_price,
                'total_price'  => $item->total_price,
            ])->values()),

            'my_items_total'  => $this->whenLoaded('items', fn () => round($this->items->sum('total_price'), 2)),

            'notes'           => $this->notes,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
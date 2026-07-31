<?php

namespace App\Http\Resources\Expert;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'sku'                 => $this->sku,
            'description'         => $this->description,

            'category'            => $this->whenLoaded('category', fn () => [
                'id'      => $this->category?->id,
                'name_ar' => $this->category?->name_ar,
                'name_en' => $this->category?->name_en,
            ]),

            'price'               => $this->price,
            'wholesale_price'     => $this->wholesale_price,

            'stock_quantity'      => (float) $this->stock_quantity,
            'min_stock_threshold' => $this->min_stock_threshold !== null ? (float) $this->min_stock_threshold : null,
            'is_low_stock'        => $this->is_low_stock,

            // متل ما طلب الـ SRS: الكمية الحالية تكفي كم شخص
            // (تتحسب من service_materials — بترجع null إذا المادة مش مربوطة بأي خدمة)
            'sufficient_for_persons' => $this->when(
                isset($this->sufficient_for_persons),
                fn () => $this->sufficient_for_persons
            ),

            'weight_grams'        => $this->weight_grams,
            'main_image'          => $this->main_image ? Storage::url($this->main_image) : null,

            'is_active'           => (bool) $this->is_active,
            'is_archived'         => (bool) $this->is_archived,

            // المواد يلي مستخدمة بأي خدمة (تطلع بس بصفحة التفاصيل)
            'used_in_services'    => $this->whenLoaded('serviceMaterials', fn () => $this->serviceMaterials->map(fn ($m) => [
                'service_id'           => $m->service_id,
                'service_name'         => $m->service?->name,
                'quantity_per_session' => (float) $m->quantity_per_session,
                'unit'                 => $m->unit,
            ])->values()),

            'last_movements'      => StockMovementResource::collection($this->whenLoaded('movements')),

            'created_at'          => $this->created_at?->toIso8601String(),
            'updated_at'          => $this->updated_at?->toIso8601String(),
        ];
    }
}

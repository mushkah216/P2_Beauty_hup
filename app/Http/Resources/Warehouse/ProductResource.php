<?php

namespace App\Http\Resources\Warehouse;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
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

            'weight_grams'        => $this->weight_grams,
            'main_image'          => $this->main_image ? Storage::url($this->main_image) : null,
            'images'              => collect($this->images_json ?? [])->map(fn ($p) => Storage::url($p))->all(),

            'is_active'           => (bool) $this->is_active,
            'is_archived'         => (bool) $this->is_archived,

            'created_at'          => $this->created_at?->toIso8601String(),
            'updated_at'          => $this->updated_at?->toIso8601String(),
        ];
    }
}
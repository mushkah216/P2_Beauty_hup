<?php

namespace App\Http\Resources\Expert;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'product_id'     => $this->product_id,
            'movement_type'  => $this->movement_type,
            'reason'         => $this->reason,
            'quantity'       => (float) $this->quantity,
            'reference_type' => $this->reference_type,
            'reference_id'   => $this->reference_id,
            'notes'          => $this->notes,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}

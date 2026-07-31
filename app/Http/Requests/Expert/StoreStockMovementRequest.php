<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'movement_type'  => 'required|in:in,out,adjustment',
            'reason'         => 'required|in:purchase,sale,booking_used,return,adjustment,expired',

            // بحالة adjustment الكمية هي الرصيد الجديد، مش الفرق
            'quantity'       => 'required|numeric|min:0',

            'reference_type' => 'nullable|string|max:50',
            'reference_id'   => 'nullable|integer',
            'notes'          => 'nullable|string|max:500',
        ];
    }
}
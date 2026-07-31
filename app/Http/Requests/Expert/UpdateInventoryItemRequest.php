<?php

namespace App\Http\Requests\Expert;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                => 'sometimes|string|max:255',
            'category_id'         => 'sometimes|nullable|integer|exists:product_categories,id',
            'description'         => 'sometimes|nullable|string|max:2000',

            'sku'                 => [
                'sometimes', 'nullable', 'string', 'max:100',
                Rule::unique('products', 'sku')
                    ->where('provider_type', Product::PROVIDER_EXPERT)
                    ->where('provider_id', $this->user()->getKey())
                    ->ignore($this->route('item')),
            ],

            'price'               => 'sometimes|nullable|numeric|min:0',
            'wholesale_price'     => 'sometimes|nullable|numeric|min:0',

            // تعديل الكمية هون = تسوية جرد، وتتسجل حركة adjustment تلقائياً
            'stock_quantity'      => 'sometimes|numeric|min:0',
            'adjustment_notes'    => 'nullable|string|max:500',

            'min_stock_threshold' => 'sometimes|nullable|numeric|min:0',
            'weight_grams'        => 'sometimes|nullable|integer|min:0',
            'main_image'          => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_active'           => 'sometimes|boolean',
        ];
    }
}

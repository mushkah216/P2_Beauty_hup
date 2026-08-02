<?php

namespace App\Http\Requests\Warehouse;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => 'sometimes|string|max:150',
            'category_id'         => 'sometimes|integer|exists:product_categories,id',
            'price'               => 'sometimes|numeric|min:0',
            'description'         => 'sometimes|nullable|string|max:2000',
            'wholesale_price'     => 'sometimes|nullable|numeric|min:0',

            // تعديل الكمية = تسوية جرد، وتتسجل حركة adjustment تلقائياً
            'stock_quantity'      => 'sometimes|numeric|min:0',
            'adjustment_notes'    => 'nullable|string|max:500',

            'min_stock_threshold' => 'sometimes|nullable|numeric|min:0',
            'weight_grams'        => 'sometimes|nullable|integer|min:0',
            'main_image'          => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_active'           => 'sometimes|boolean',

            'sku' => [
                'sometimes', 'nullable', 'string', 'max:50',
                Rule::unique('products', 'sku')
                    ->where('provider_type', Product::PROVIDER_WAREHOUSE)
                    ->where('provider_id', $this->user()->getKey())
                    ->ignore($this->route('product')),
            ],
        ];
    }
}
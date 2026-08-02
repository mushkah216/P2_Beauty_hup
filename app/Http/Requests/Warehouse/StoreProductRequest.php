<?php

namespace App\Http\Requests\Warehouse;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // بعكس مواد الخبير، منتجات المخزن للبيع — فالسعر والتصنيف إلزاميين
            'name'                => 'required|string|max:150',
            'category_id'         => 'required|integer|exists:product_categories,id',
            'price'               => 'required|numeric|min:0',
            'description'         => 'nullable|string|max:2000',
            'wholesale_price'     => 'nullable|numeric|min:0',
            'stock_quantity'      => 'required|numeric|min:0',
            'min_stock_threshold' => 'nullable|numeric|min:0',
            'weight_grams'        => 'nullable|integer|min:0',
            'main_image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'images'              => 'nullable|array|max:6',
            'images.*'            => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_active'           => 'nullable|boolean',
            'notes'               => 'nullable|string|max:500',

            'sku' => [
                'nullable', 'string', 'max:50',
                Rule::unique('products', 'sku')
                    ->where('provider_type', Product::PROVIDER_WAREHOUSE)
                    ->where('provider_id', $this->user()->getKey()),
            ],
        ];
    }
}
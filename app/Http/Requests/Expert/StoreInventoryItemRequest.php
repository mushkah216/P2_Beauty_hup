<?php

namespace App\Http\Requests\Expert;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemRequest extends FormRequest
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
            'name'                => 'required|string|max:255',
            'category_id'         => 'nullable|integer|exists:product_categories,id',
            'description'         => 'nullable|string|max:2000',

            // هون بس اضطريت أستخدم array لأن Rule::unique ما بينحط بـ string
            // (لازم SKU يكون فريد عند نفس الخبير، مش على كل الجدول)
            'sku'                 => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'sku')
                    ->where('provider_type', Product::PROVIDER_EXPERT)
                    ->where('provider_id', $this->user()->getKey()),
            ],

            'price'               => 'nullable|numeric|min:0',
            'wholesale_price'     => 'nullable|numeric|min:0',
            'stock_quantity'      => 'required|numeric|min:0',
            'min_stock_threshold' => 'nullable|numeric|min:0',
            'weight_grams'        => 'nullable|integer|min:0',
            'main_image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'images'              => 'nullable|array|max:6',
            'images.*'            => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_active'           => 'nullable|boolean',
            'notes'               => 'nullable|string|max:500',
        ];
    }
    }


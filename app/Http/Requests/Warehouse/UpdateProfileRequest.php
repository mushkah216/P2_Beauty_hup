<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'sometimes|string|max:255',
            'phone'          => 'sometimes|string|max:20',
            'description'    => 'sometimes|nullable|string|max:2000',
            'governorate'    => 'sometimes|string|max:100',
            'city'           => 'sometimes|string|max:100',
            'address_detail' => 'sometimes|nullable|string|max:255',
            'location_lat'   => 'sometimes|nullable|numeric|between:-90,90',
            'location_lng'   => 'sometimes|nullable|numeric|between:-180,180',

            'email' => [
                'sometimes', 'email', 'max:255',
                Rule::unique('warehouses', 'email')->ignore($this->user()->getKey()),
            ],
        ];
    }
}
<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:warehouses,email',
            'phone'          => 'required|string|max:20',
            'password'       => 'required|string|min:8|confirmed',
            'description'    => 'nullable|string|max:2000',
            'governorate'    => 'required|string|max:100',
            'city'           => 'required|string|max:100',
            'address_detail' => 'nullable|string|max:255',
            'location_lat'   => 'nullable|numeric|between:-90,90',
            'location_lng'   => 'nullable|numeric|between:-180,180',
        ];
    }
}
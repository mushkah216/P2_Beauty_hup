<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class SyncServiceMaterialsRequest extends FormRequest
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
            'materials'                        => 'present|array',
            'materials.*.product_id'           => 'required|integer|exists:products,id',
            'materials.*.quantity_per_session' => 'required|numeric|gt:0',
            'materials.*.unit'                 => 'nullable|string|max:20',
        ];
    }
}

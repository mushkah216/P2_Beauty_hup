<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
        'category_id'               => ['sometimes', 'nullable', 'exists:service_categories,id'],
        'name'                      => ['required', 'string', 'max:150'],
        'description'               => ['sometimes', 'nullable', 'string'],
        'price'                     => ['required', 'numeric', 'min:0'],
        'duration_minutes'          => ['required', 'integer', 'min:5'],
        'deposit_percent'           => ['sometimes', 'numeric', 'min:0', 'max:100'],
        'cancellation_deadline_hrs' => ['sometimes', 'integer', 'min:0'],
        'gender_for'                => ['sometimes', 'in:male,female,both'],
    ];
    }
}

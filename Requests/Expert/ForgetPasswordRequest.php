<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class ForgetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:experts,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'البريد الإلكتروني غير مسجل.',
        ];
    }
}

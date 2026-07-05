<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class ResendOtpRequest extends FormRequest
{
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

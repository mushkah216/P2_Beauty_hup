<?php

namespace App\Http\Requests\Expert;

use App\Rules\OTPValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'        => ['required', 'email', 'exists:experts,email'],
            'otp'          => ['required', new OTPValidationRule('experts')],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists'             => 'البريد الإلكتروني غير مسجل.',
            'new_password.confirmed'   => 'تأكيد كلمة المرور غير متطابق.',
            'new_password.min'         => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
        ];
    }
}

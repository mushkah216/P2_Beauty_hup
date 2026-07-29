<?php

namespace App\Http\Requests\Auth;

use App\Rules\OTPValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'otp'   => ['required', 'string', 'size:6', new OTPValidationRule('users')],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.exists'   => 'البريد الإلكتروني غير مسجل.',
            'otp.required'   => 'رمز التحقق مطلوب.',
            'otp.size'       => 'رمز التحقق يجب أن يكون 6 أرقام.',
        ];
    }
}
<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class ChangePasswordRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'كلمة المرور الحالية مطلوبة.',
            'new_password.required'     => 'كلمة المرور الجديدة مطلوبة.',
            'new_password.min'          => 'كلمة المرور الجديدة يجب ألا تقل عن 8 أحرف.',
            'new_password.confirmed'    => 'تأكيد كلمة المرور الجديدة غير متطابق.',
        ];
    }
}
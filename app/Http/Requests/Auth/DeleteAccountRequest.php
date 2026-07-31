<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class DeleteAccountRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'كلمة المرور مطلوبة لتأكيد حذف الحساب.',
        ];
    }
}
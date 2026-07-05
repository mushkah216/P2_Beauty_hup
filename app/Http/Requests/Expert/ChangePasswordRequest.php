<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
        'current_password' => ['required', 'string'], // ← شيلي الـ closure
        'new_password'     => [
            'required',
            'string',
            'min:8',
            'confirmed',
            'different:current_password',
        ],
    ];
    }

    public function messages(): array
    {
        return [
            'new_password.confirmed'  => 'تأكيد كلمة المرور غير متطابق.',
            'new_password.min'        => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'new_password.different'  => 'كلمة المرور الجديدة يجب أن تكون مختلفة عن الحالية.',
        ];
    }
}

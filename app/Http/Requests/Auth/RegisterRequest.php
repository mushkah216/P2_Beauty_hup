<?php

namespace App\Http\Requests\Auth;

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
            'full_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'      => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'birth_date' => ['nullable', 'date'],
            'gender'     => ['nullable', 'in:male,female'],
            'language'   => ['nullable', 'in:ar,en'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'الاسم الكامل مطلوب.',
            'email.required'     => 'البريد الإلكتروني مطلوب.',
            'email.email'        => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'       => 'هذا البريد مستخدم مسبقاً.',
            'password.required'  => 'كلمة المرور مطلوبة.',
            'password.confirmed'  => 'تأكيد كلمة المرور غير متطابق.',
        ];
    }
}
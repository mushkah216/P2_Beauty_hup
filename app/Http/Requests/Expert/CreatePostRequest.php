<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
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
            'caption' => ['nullable', 'string', 'max:2200'],
            'media'   => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,mp4,mov', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'media.*.mimes' => 'صيغة الملف غير مدعومة',
            'media.*.max'   => 'حجم الملف كبير كتير',
            'media.max'     => 'ما فيك تضيف أكتر من 10 عناصر وسائط',
        ];
    }
}

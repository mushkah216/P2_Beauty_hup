<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class CreateStoryRequest extends FormRequest
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
            'media'   => ['nullable', 'required_without:caption', 'file', 'mimes:jpg,jpeg,png,mp4,mov', 'max:20480'],
            'caption' => ['nullable', 'required_without:media', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required_without'   => 'لازم تحط ميديا أو نص للستوري',
            'caption.required_without' => 'لازم تحط ميديا أو نص للستوري',
            'media.mimes'               => 'صيغة الملف غير مدعومة',
            'media.max'                 => 'حجم الملف كبير كتير',
        ];
    }
}

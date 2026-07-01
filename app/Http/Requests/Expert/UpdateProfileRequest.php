<?php

namespace App\Http\Requests\Expert;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $expertId = auth()->user()->id;

        return [
            'full_name'            => ['sometimes', 'string', 'max:100'],
            'phone'                => ['sometimes', 'string', Rule::unique('experts', 'phone')->ignore($expertId)],
            'bio'                  => ['sometimes', 'nullable', 'string'],
            'specialization'       => ['sometimes', 'string', 'max:150'],
            'experience_years'     => ['sometimes', 'integer', 'min:0', 'max:50'],
            'governorate'          => ['sometimes', 'nullable', 'string', 'max:80'],
            'city'                 => ['sometimes', 'nullable', 'string', 'max:80'],
            'location_lat'         => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'location_lng'         => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'service_area_km'      => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'is_available_for_hire'=> ['sometimes', 'boolean'],
            'birth_date'           => ['sometimes', 'nullable', 'date', 'before:today'],
            'profile_photo'        => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_photo'          => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique'                => 'رقم الهاتف مستخدم من قِبل حساب آخر.',
            'location_lat.between'        => 'خط العرض يجب أن يكون بين -90 و 90.',
            'location_lng.between'        => 'خط الطول يجب أن يكون بين -180 و 180.',
            'experience_years.max'        => 'سنوات الخبرة لا يمكن أن تتجاوز 50.',
            'birth_date.before'           => 'تاريخ الميلاد يجب أن يكون قبل اليوم.',
            'profile_photo.mimes'         => 'صورة الملف الشخصي يجب أن تكون jpg أو png أو webp.',
            'cover_photo.mimes'           => 'صورة الغلاف يجب أن تكون jpg أو png أو webp.',
            'profile_photo.max'           => 'حجم الصورة يجب أن لا يتجاوز 2MB.',
            'cover_photo.max'             => 'حجم الصورة يجب أن لا يتجاوز 2MB.',
        ];
    }
}

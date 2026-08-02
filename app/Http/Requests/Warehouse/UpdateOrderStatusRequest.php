<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // بعد القبول، المخزن بيحرّك الطلب بهالثلاث حالات بس
            'status' => 'required|in:processing,shipped,delivered',
        ];
    }
}
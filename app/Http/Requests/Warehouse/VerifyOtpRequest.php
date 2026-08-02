<?php

namespace App\Http\Requests\Warehouse;

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
            'email' => 'required|email|exists:warehouses,email',
            'otp'   => ['required', 'digits:6', new OTPValidationRule('warehouses')],
        ];
    }
}
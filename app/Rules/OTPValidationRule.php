<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OTPValidationRule implements ValidationRule
{
    public function __construct(protected string $table) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $record = DB::table($this->table)
            ->where('email', request('email'))
            ->first();

        if (!$record) {
            $fail('البريد الإلكتروني غير موجود.');
            return;
        }

        if (!$record->otp_code || !Hash::check($value, $record->otp_code)) {
            $fail('كود التحقق غير صحيح.');
            return;
        }

        if (now()->isAfter($record->otp_expires_at)) {
            $fail('انتهت صلاحية كود التحقق.');
        }
    }
}
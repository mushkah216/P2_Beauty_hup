<?php

namespace App\Rules;

use App\Models\Expert;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class OTPValidationRule implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function __construct(
        protected string $table = 'users',
        protected string $emailField = 'email'
    ) {
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = $this->data[$this->emailField] ?? null;

        if (!is_string($email) || trim($email) === '') {
            $fail('البريد الإلكتروني مطلوب للتحقق.');
            return;
        }

        $modelClass = $this->resolveModelClass();

        $user = $modelClass::where('email', $email)->first();

        if (!$user) {
            $fail('الحساب غير موجود.');
            return;
        }

        if (!$user->otp_code || !$user->otp_expires_at) {
            $fail('لا يوجد رمز تحقق صالح.');
            return;
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            $fail('انتهت صلاحية رمز التحقق.');
            return;
        }

        if (!Hash::check((string) $value, $user->otp_code)) {
            $fail('رمز التحقق غير صحيح.');
        }
    }

    private function resolveModelClass(): string
    {
        return match ($this->table) {
            'users' => User::class,
            'experts' => Expert::class,
            default => throw new \InvalidArgumentException("Unsupported table [{$this->table}]."),
        };
    }
}
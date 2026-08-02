<?php

namespace App\Services\Warehouse;

use App\Models\Warehouse;
use App\Notifications\OTPNotification;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WarehouseService
{
    use ApiResponseTrait;

    // POST /auth/warehouse/register
    public function register(array $input)
    {
        $warehouse = Warehouse::create([
            'name'           => $input['name'],
            'email'          => $input['email'],
            'phone'          => $input['phone'],
            'password'       => $input['password'], // الكاست 'hashed' بيشفّره لحاله
            'description'    => $input['description'] ?? null,
            'governorate'    => $input['governorate'],
            'city'           => $input['city'],
            'address_detail' => $input['address_detail'] ?? null,
            'location_lat'   => $input['location_lat'] ?? null,
            'location_lng'   => $input['location_lng'] ?? null,
            // account_status بيضل 'pending' بالـ default لحتى يوافق السوبر أدمن
        ]);

        $warehouse->notify(new OTPNotification());

        return $this->sendResponse([], 'تم إنشاء الحساب. تحقق من بريدك الإلكتروني لرمز التحقق.');
    }

    // POST /auth/warehouse/verify-otp
    // الرمز بيتحقق منه بالـ OTPValidationRule داخل الـ Request
    public function verifyOtp(array $input)
    {
        $warehouse = Warehouse::where('email', $input['email'])->firstOrFail();

        $warehouse->update([
            'email_verified_at' => now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
        ]);

        return $this->sendResponse([], 'تم تأكيد البريد الإلكتروني. بانتظار موافقة الإدارة على الحساب.');
    }

    // POST /auth/warehouse/resend-otp
    public function resendOtp(array $input)
    {
        $warehouse = Warehouse::where('email', $input['email'])->firstOrFail();
        $warehouse->notify(new OTPNotification());

        return $this->sendResponse([], 'تم إرسال رمز تحقق جديد.');
    }

    // POST /auth/warehouse/login
    public function login(array $input)
    {
        $warehouse = Warehouse::where('email', $input['email'])->first();

        if (! $warehouse || ! Hash::check($input['password'], $warehouse->password)) {
            return $this->sendError('بيانات الدخول غير صحيحة.', 401);
        }

        if (! $warehouse->email_verified_at) {
            return $this->sendError('يرجى تأكيد بريدك الإلكتروني أولاً.', 403);
        }

        if ($warehouse->account_status !== 'active') {
            return $this->sendError('حسابك غير مفعّل بعد. يرجى انتظار موافقة الإدارة.', 403);
        }

        $warehouse->update(['last_login_at' => now()]);

        $token = $warehouse->createToken('warehouse-token')->plainTextToken;

        return $this->sendResponse(['token' => $token], 'تم تسجيل الدخول بنجاح.');
    }

    // POST /warehouse/logout
    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'تم تسجيل الخروج بنجاح.');
    }

    // GET /warehouse/profile
    public function profile()
    {
        return $this->sendResponse(['warehouse' => Auth::user()], 'تم جلب الملف الشخصي.');
    }

    // PUT /warehouse/profile
    public function updateProfile(array $input)
    {
        $warehouse = Auth::user();
        $warehouse->update($input);

        return $this->sendResponse(['warehouse' => $warehouse->fresh()], 'تم تحديث الملف الشخصي.');
    }
}
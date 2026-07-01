<?php

namespace App\Services\Expert;

use App\Models\Expert;
use App\Notifications\OTPNotification;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ExpertService
{
    use ApiResponseTrait;
    

    // POST /expert/auth/register
    public function register(array $input)
{
    $expert = Expert::create([
        'full_name'      => $input['full_name'],   // ليس first_name/last_name
        'email'          => $input['email'],
        'phone'          => $input['phone'],
        'specialization' => $input['specialization'],
        'password_hash'  => Hash::make($input['password']),
        // account_status يبقى 'pending' بالـ default
    ]);

    $expert->notify(new OTPNotification());

    return $this->sendResponse([], 'Registered successfully. Check your email for OTP.');
}

    // POST /expert/auth/verify_otp
    // OTP بيتحقق منه عن طريق OTPValidationRule بالـ Request
    public function verifyOtp(array $input)
    {
        $expert = Expert::where('email', $input['email'])->firstOrFail();

        $expert->update([
            'email_verified_at' => now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
        ]);

        return $this->sendResponse([], 'Email verified successfully. You can now login.');
    }

    // POST /expert/auth/resend_otp
    public function resendOtp(array $input)
    {
        $expert = Expert::where('email', $input['email'])->firstOrFail();
        $expert->notify(new OTPNotification());
        return $this->sendResponse([], 'OTP resent. Check your email.');
    }

    // POST /expert/auth/login
    public function login(array $input)
    {
        $expert = Expert::where('email', $input['email'])->first();

       if (!$expert || !Hash::check($input['password'], $expert->password_hash)) {
            return $this->sendError('Invalid credentials.', 401);
        }

        if (!$expert->email_verified_at) {
            return $this->sendError('Please verify your email first.', 403);
        }

        $token = $expert->createToken('expert-token')->plainTextToken;

        return $this->sendResponse(['token' => $token], 'Logged in successfully.');
    }

    // POST /expert/auth/logout
    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->sendResponse([], 'Logged out successfully.');
    }

    // POST /expert/auth/forget_password
    public function forgetPassword(array $input)
    {
        $expert = Expert::where('email', $input['email'])->firstOrFail();
        $expert->notify(new OTPNotification());
        return $this->sendResponse([], 'Check your email for OTP.');
    }

    // POST /expert/auth/reset_password
    // OTP بيتحقق منه بالـ Request
    public function resetPassword(array $input)
    {
        $expert = Expert::where('email', $input['email'])->firstOrFail();

        $expert->update([
            'password_hash'       => Hash::make($input['new_password']),
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        return $this->sendResponse([], 'Password reset successfully.');
    }

    // GET /expert/auth/profile
    public function profile()
    {
        $expert = Auth::user();
        return $this->sendResponse(['expert' => $expert], 'Profile retrieved successfully.');
    }

    // POST /expert/auth/update_profile
    public function updateProfile(array $input)
    {
        $expert = Auth::user();
        $expert->update($input);
        return $this->sendResponse([], 'Profile updated successfully.');
    }

    // POST /expert/auth/change_password (وهو مسجّل دخول)
    public function changePassword(array $input)
{
    $expert = Auth::user();

    if (!Hash::check($input['current_password'], $expert->password_hash)) {
        return $this->sendError('Current password is incorrect.', 422);
    }

    $expert->update(['password_hash' => Hash::make($input['new_password'])]);
    $expert->tokens()->delete();

    return $this->sendResponse([], 'Password changed successfully. Please login again.');
}

    // DELETE /expert/auth/delete_account
    public function deleteAccount()
    {
        $expert = Auth::user();
        $expert->tokens()->delete();
        $expert->delete();
        return $this->sendResponse([], 'Account deleted successfully.');
    }
}
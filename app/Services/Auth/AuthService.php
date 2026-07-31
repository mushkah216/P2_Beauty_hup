<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\OTPNotification;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    use ApiResponseTrait;

    public function guestLogin()
    {
        return $this->sendResponse([
            'mode' => 'guest',
            'permissions' => [
                'home',
                'search',
                'view_providers',
                'view_services',
                'view_posts',
            ],
        ], 'Guest mode enabled.');
    }

    public function register(array $input)
    {
        $user = User::create([
            'full_name'             => $input['full_name'],
            'email'                 => $input['email'],
            'phone'                 => $input['phone'] ?? null,
            'birth_date'            => $input['birth_date'] ?? null,
            'gender'                => $input['gender'] ?? null,
            'language'              => $input['language'] ?? 'ar',
            'password'              => Hash::make($input['password']),
            'loyalty_points'        => 0,
            'notifications_enabled' => true,
            'is_active'             => true,
            'last_login_at'         => null,
            'email_verified_at'     => null,
        ]);

        $otp = $this->issueOtp(
            $user,
            'Beauty Hub — رمز التحقق',
            'رمز التحقق الخاص بك هو:'
        );

        $payload = [
            'user' => $user->only([
                'id',
                'full_name',
                'email',
                'phone',
                'birth_date',
                'gender',
                'language',
            ]),
            'requires_verification' => true,
        ];

        if (app()->environment(['local', 'testing'])) {
            $payload['debug_otp'] = $otp;
        }

        return $this->sendResponse(
            $payload,
            'Account created successfully. Check your email for the OTP.',
            201
        );
    }

    public function resendOtp(array $input)
    {
        $user = User::where('email', $input['email'])->first();

        if (!$user) {
            return $this->sendError('User not found.', 404);
        }

        if ($user->email_verified_at) {
            return $this->sendError('Email is already verified.', 409);
        }

        $otp = $this->issueOtp(
            $user,
            'Beauty Hub — إعادة إرسال رمز التحقق',
            'رمز التحقق الجديد الخاص بك هو:'
        );

        $payload = [
            'requires_verification' => true,
        ];

        if (app()->environment(['local', 'testing'])) {
            $payload['debug_otp'] = $otp;
        }

        return $this->sendResponse($payload, 'OTP resent successfully.');
    }

    public function verifyOtp(array $input)
    {
        $user = User::where('email', $input['email'])->first();

        if (!$user) {
            return $this->sendError('User not found.', 404);
        }

        if ($user->email_verified_at) {
            return $this->sendResponse([], 'Email already verified.');
        }

        if (!$this->isOtpValid($user, $input['otp'])) {
            return $this->sendError('Invalid or expired OTP.', 422);
        }

        $user->update([
            'email_verified_at' => now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
        ]);

        return $this->sendResponse([], 'OTP verified successfully. You can now login.');
    }

    public function login(array $input)
    {
        $user = User::where('email', $input['email'])->first();

        if (!$user || !Hash::check($input['password'], $user->password)) {
            return $this->sendError('Invalid credentials.', 401);
        }

        if (!$user->email_verified_at) {
            return $this->sendError('Please verify your email first.', 403);
        }

        if (!$user->is_active) {
            return $this->sendError('Your account is disabled.', 403);
        }

        $user->update([
            'last_login_at' => now(),
        ]);

        $token = $user->createToken('customer-token')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'user'  => $user,
        ], 'Logged in successfully.');
    }

    public function forgotPassword(array $input)
    {
        $user = User::where('email', $input['email'])->first();

        if (!$user) {
            return $this->sendError('User not found.', 404);
        }

        $otp = $this->issueOtp(
            $user,
            'Beauty Hub — إعادة تعيين كلمة المرور',
            'رمز إعادة تعيين كلمة المرور هو:'
        );

        $payload = [];

        if (app()->environment(['local', 'testing'])) {
            $payload['debug_otp'] = $otp;
        }

        return $this->sendResponse($payload, 'OTP sent to your email.');
    }

    public function resetPassword(array $input)
    {
        $user = User::where('email', $input['email'])->first();

        if (!$user) {
            return $this->sendError('User not found.', 404);
        }

        if (!$this->isOtpValid($user, $input['otp'])) {
            return $this->sendError('Invalid or expired OTP.', 422);
        }

        $user->update([
            'password'       => Hash::make($input['new_password']),
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        return $this->sendResponse([], 'Password reset successfully.');
    }

    public function changePassword(array $input)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('Unauthorized.', 401);
        }

        if (!Hash::check($input['current_password'], $user->password)) {
            return $this->sendError('Current password is incorrect.', 422);
        }

        $user->update([
            'password' => Hash::make($input['new_password']),
        ]);

        $user->tokens()->delete();

        return $this->sendResponse([], 'Password changed successfully. Please login again.');
    }

    public function logout()
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('Unauthorized.', 401);
        }

        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return $this->sendResponse([], 'Logged out successfully.');
    }

    public function deleteAccount(array $input)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('Unauthorized.', 401);
        }

        if (!Hash::check($input['password'], $user->password)) {
            return $this->sendError('Password is incorrect.', 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return $this->sendResponse([], 'Account deleted successfully.');
    }

    private function issueOtp(User $user, string $subject, string $headline): string
    {
        $otp = (string) random_int(100000, 999999);

        $user->update([
            'otp_code'      => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new OTPNotification(
            otp: $otp,
            subject: $subject,
            headline: $headline,
            description: $headline
        ));

        return $otp;
    }

    private function isOtpValid(User $user, string $otp): bool
    {
        if (!$user->otp_code || !$user->otp_expires_at) {
            return false;
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return false;
        }

        return Hash::check($otp, $user->otp_code);
    }
}
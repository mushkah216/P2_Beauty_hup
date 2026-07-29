<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\DeleteAccountRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    protected AuthService $authService;

    /**
     * Constructor
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Guest Login
     */
    public function guestLogin(): JsonResponse
    {
        return $this->authService->guestLogin();
    }

    /**
     * Register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->authService->register(
            $request->validated()
        );
    }

    /**
     * Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->authService->login(
            $request->validated()
        );
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        return $this->authService->verifyOtp(
            $request->validated()
        );
    }

    /**
     * Resend OTP
     */
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        return $this->authService->resendOtp(
            $request->validated()
        );
    }

    /**
     * Forgot Password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->authService->forgotPassword(
            $request->validated()
        );
    }

    /**
     * Reset Password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return $this->authService->resetPassword(
            $request->validated()
        );
    }

    /**
     * Change Password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        return $this->authService->changePassword(
            $request->validated()
        );
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        return $this->authService->logout();
    }

    /**
     * Delete Account
     */
    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        return $this->authService->deleteAccount(
            $request->validated()
        );
    }
}
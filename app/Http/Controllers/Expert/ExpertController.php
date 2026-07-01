<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\ChangePasswordRequest;
use App\Http\Requests\Expert\ForgetPasswordRequest;
use App\Http\Requests\Expert\LoginRequest;
use App\Http\Requests\Expert\RegisterRequest;
use App\Http\Requests\Expert\ResendOtpRequest;
use App\Http\Requests\Expert\ResetPasswordRequest;
use App\Http\Requests\Expert\UpdateProfileRequest;
use App\Http\Requests\Expert\VerifyOtpRequest;
use App\Services\Expert\ExpertService;

class ExpertController extends Controller
{
    public ExpertService $expertService;

    public function __construct(ExpertService $expertService)
    {
        $this->expertService = $expertService;
    }

    public function register(RegisterRequest $request)
    {
        return $this->expertService->register($request->validated());
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        return $this->expertService->verifyOtp($request->validated());
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        return $this->expertService->resendOtp($request->validated());
    }

    public function login(LoginRequest $request)
    {
        return $this->expertService->login($request->validated());
    }

    public function logout()
    {
        return $this->expertService->logout();
    }

    public function forgetPassword(ForgetPasswordRequest $request)
    {
        return $this->expertService->forgetPassword($request->validated());
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        return $this->expertService->resetPassword($request->validated());
    }

    public function profile()
    {
        return $this->expertService->profile();
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        return $this->expertService->updateProfile($request->validated());
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->expertService->changePassword($request->validated());
    }

    public function deleteAccount()
    {
        return $this->expertService->deleteAccount();
    }
}
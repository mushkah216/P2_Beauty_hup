<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\LoginRequest;
use App\Http\Requests\Warehouse\RegisterRequest;
use App\Http\Requests\Warehouse\ResendOtpRequest;
use App\Http\Requests\Warehouse\UpdateProfileRequest;
use App\Http\Requests\Warehouse\VerifyOtpRequest;
use App\Services\Warehouse\WarehouseService;

class WarehouseController extends Controller
{
    public WarehouseService $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function register(RegisterRequest $request)
    {
        return $this->warehouseService->register($request->validated());
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        return $this->warehouseService->verifyOtp($request->validated());
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        return $this->warehouseService->resendOtp($request->validated());
    }

    public function login(LoginRequest $request)
    {
        return $this->warehouseService->login($request->validated());
    }

    public function logout()
    {
        return $this->warehouseService->logout();
    }

    public function profile()
    {
        return $this->warehouseService->profile();
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        return $this->warehouseService->updateProfile($request->validated());
    }
}
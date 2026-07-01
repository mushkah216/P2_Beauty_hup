<?php

use App\Http\Controllers\Expert\ExpertController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//expert 
Route::prefix('expert')->group(function () {
    Route::prefix('auth')->group(function () {

        // Guest routes
        Route::post('register',        [ExpertController::class, 'register']);
        Route::post('verify_otp',      [ExpertController::class, 'verifyOtp']);
        Route::post('resend_otp',      [ExpertController::class, 'resendOtp']);
        Route::post('forget_password', [ExpertController::class, 'forgetPassword']);
        Route::post('reset_password',  [ExpertController::class, 'resetPassword']);
        Route::post('login',           [ExpertController::class, 'login'])
             ->middleware('expert.is_banned');

        // Authenticated routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout',          [ExpertController::class, 'logout']);
            Route::get('profile',          [ExpertController::class, 'profile']);
            Route::post('update_profile',  [ExpertController::class, 'updateProfile']);
            Route::post('change_password', [ExpertController::class, 'changePassword']);
            Route::delete('delete_account',[ExpertController::class, 'deleteAccount']);
        });
    });
});
<?php

use App\Http\Controllers\Expert\ExpertController;
use App\Http\Controllers\Expert\PostController;
use App\Http\Controllers\Expert\StoryController;
use App\Http\Controllers\ServiceController;
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


Route::prefix('expert')->middleware(['auth:sanctum', 'expert.is_active'])->group(function () {

    // posts
    Route::get('getMyPosts',            [PostController::class, 'getMyPosts']);
    Route::post('createPost',           [PostController::class, 'createPost']);
    Route::get('getPostDetails/{post}', [PostController::class, 'getPostDetails']);
    Route::post('updatePost/{post}',     [PostController::class, 'updatePost']);
    Route::delete('deletePost/{post}',  [PostController::class, 'deletePost']);

     //story
     Route::get('getMyStories',         [StoryController::class, 'getMyStories']);
     Route::post('createStory',         [StoryController::class, 'createStory']);
     Route::delete('deleteStory/{story}',  [StoryController::class, 'deleteStory']);
       
   

Route::middleware(['auth:sanctum', 'expert.is_active'])->group(function () {

    // Services CRUD
    Route::get   ('services',                        [ServiceController::class, 'index']);
    Route::post  ('services',                        [ServiceController::class, 'store']);
    Route::post   ('services/{service}',              [ServiceController::class, 'update']);
    Route::delete('services/{service}',              [ServiceController::class, 'destroy']);

    // Service Instructions
    Route::post  ('services/{service}/instructions', [ServiceController::class, 'updateInstructions']);

    // Pre-Booking Questions
    Route::post  ('services/{service}/questions',    [ServiceController::class, 'setQuestions']);

    // Min Bookings Remote
    Route::post   ('services/{service}/min-bookings', [ServiceController::class, 'updateMinBookings']);
    });
});
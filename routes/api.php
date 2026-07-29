<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Expert\ExpertController;
use App\Http\Controllers\Expert\PostController;
use App\Http\Controllers\Expert\StoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Customer / User Side
|--------------------------------------------------------------------------
*/
Route::prefix('customer')->name('customer.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->name('auth.')->controller(AuthController::class)->group(function () {
        Route::post('guest_login', 'guestLogin')->name('guest_login');
        Route::post('register', 'register')->name('register');
        Route::post('resend_otp', 'resendOtp')->name('resend_otp');
        Route::post('verify_otp', 'verifyOtp')->name('verify_otp');
        Route::post('login', 'login')->name('login');
        Route::post('forgot_password', 'forgotPassword')->name('forgot_password');
        Route::post('reset_password', 'resetPassword')->name('reset_password');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('change_password', 'changePassword')->name('change_password');
            Route::post('logout', 'logout')->name('logout');
            Route::delete('delete_account', 'deleteAccount')->name('delete_account');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Profile / Medical / Addresses
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->controller(CustomerController::class)->group(function () {
        Route::get('profile', 'showProfile')->name('profile.show');
        Route::post('update_profile', 'updateProfile')->name('profile.update');

        Route::get('medical_record', 'showMedicalRecord')->name('medical_record.show');
        Route::post('medical_record', 'updateMedicalRecord')->name('medical_record.update');

        Route::get('addresses', 'listAddresses')->name('addresses.index');
        Route::post('addresses', 'storeAddress')->name('addresses.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Public / Discovery
    |--------------------------------------------------------------------------
    */
    Route::get('selected-service-state', [CustomerController::class, 'selectedServiceState'])
        ->name('selected_service_state');

    Route::middleware('auth:sanctum')->controller(CustomerController::class)->group(function () {
        Route::get('home', 'home')->name('home');

        Route::get('providers/top-rated/salons', 'topRatedSalons')->name('providers.top_rated.salons');
        Route::get('providers/top-rated/beauty-centers', 'topRatedBeautyCenters')->name('providers.top_rated.beauty_centers');
        Route::get('providers/top-rated/experts', 'topRatedExperts')->name('providers.top_rated.experts');

        Route::get('providers/search', 'searchProviders')->name('providers.search');
        Route::get('providers/{type}/{id}', 'showProvider')->name('providers.show');
        Route::get('providers/{type}/{id}/posts', 'providerPosts')->name('providers.posts');

        Route::get('feed/posts', 'feedPosts')->name('feed.posts');

        Route::get('providers/filter/nearby', 'filterNearby')->name('providers.filter.nearby');
        Route::get('providers/filter/governorate', 'filterByGovernorate')->name('providers.filter.governorate');
        Route::get('providers/filter/city', 'filterByCity')->name('providers.filter.city');
        Route::get('providers/filter/service-type', 'filterByServiceType')->name('providers.filter.service_type');
        Route::get('providers/filter/price', 'filterByPrice')->name('providers.filter.price');

        Route::get('providers/{type}/{id}/employees', 'providerEmployees')->name('providers.employees');
        Route::get('providers/{type}/{id}/services', 'providerServices')->name('providers.services');
        Route::get('services/{id}', 'serviceDetails')->name('services.show');
        Route::get('services/{id}/questions', 'serviceQuestions')->name('services.questions');

        Route::get('providers/{type}/{id}/schedule', 'providerSchedule')->name('providers.schedule');
        Route::get('providers/{type}/{id}/employees/{employeeId}/schedule', 'employeeSchedule')->name('providers.employee_schedule');
    });

    /*
    |--------------------------------------------------------------------------
    | Posts / Social
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->controller(CustomerController::class)->group(function () {
        Route::get('posts/{id}', 'postDetails')->name('posts.show');
        Route::get('posts/{id}/comments', 'postComments')->name('posts.comments.index');
        Route::post('posts/{id}/comments', 'addPostComment')->name('posts.comments.store');
        Route::post('posts/{id}/comments/{commentId}/reply', 'replyToComment')->name('posts.comments.reply');
        Route::post('posts/{id}/like', 'toggleLikePost')->name('posts.like');
        Route::post('posts/{id}/favorite', 'toggleFavoritePost')->name('posts.favorite');

        Route::post('providers/{type}/{id}/follow', 'toggleFollowProvider')->name('providers.follow');
        Route::post('providers/{type}/{id}/block', 'toggleBlockProvider')->name('providers.block');
        Route::post('users/{id}/block', 'toggleBlockUser')->name('users.block');
    });

    /*
    |--------------------------------------------------------------------------
    | Bookings
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->controller(CustomerController::class)->group(function () {
        Route::post('bookings', 'storeBooking')->name('bookings.store');
        Route::get('bookings/{id}', 'showBooking')->name('bookings.show');
        Route::get('bookings/{id}/status', 'bookingStatus')->name('bookings.status');
        Route::post('bookings/{id}/cancel', 'cancelBooking')->name('bookings.cancel');
        Route::post('bookings/{id}/reschedule', 'rescheduleBooking')->name('bookings.reschedule');
        Route::post('bookings/{id}/rate', 'rateBooking')->name('bookings.rate');
        Route::post('bookings/{id}/report', 'reportBooking')->name('bookings.report');
        Route::get('bookings/{id}/invoice', 'bookingInvoice')->name('bookings.invoice');
        Route::get('bookings/history', 'bookingHistory')->name('bookings.history');
        Route::get('bookings/upcoming', 'upcomingBookings')->name('bookings.upcoming');
        Route::get('bookings/{id}/details', 'bookingDetails')->name('bookings.details');
        Route::get('bookings/{id}/services', 'bookingServices')->name('bookings.services');
        Route::get('bookings/{id}/employee', 'bookingEmployee')->name('bookings.employee');

        Route::post('bookings/preview', 'previewBooking')->name('bookings.preview');
        Route::post('bookings/service-selection', 'storeServiceSelection')->name('bookings.service_selection');
        Route::post('bookings/answers', 'storeBookingAnswers')->name('bookings.answers');
        Route::get('bookings/summary', 'bookingSummary')->name('bookings.summary');
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->controller(CustomerController::class)->group(function () {
        Route::get('notifications', 'notifications')->name('notifications.index');
        Route::post('notifications/{id}/read', 'markNotificationRead')->name('notifications.read');
    });

    /*
    |--------------------------------------------------------------------------
    | Wallet
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->controller(CustomerController::class)->group(function () {
        Route::get('wallet', 'wallet')->name('wallet.show');
        Route::post('wallet/withdraw', 'withdrawFromWallet')->name('wallet.withdraw');
        Route::get('wallet/transactions', 'walletTransactions')->name('wallet.transactions');
    });
});

/*
|--------------------------------------------------------------------------
| Expert Side
|--------------------------------------------------------------------------
*/
Route::prefix('expert')->name('expert.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [ExpertController::class, 'register'])->name('register');
        Route::post('verify_otp', [ExpertController::class, 'verifyOtp'])->name('verify_otp');
        Route::post('resend_otp', [ExpertController::class, 'resendOtp'])->name('resend_otp');
        Route::post('forget_password', [ExpertController::class, 'forgetPassword'])->name('forget_password');
        Route::post('reset_password', [ExpertController::class, 'resetPassword'])->name('reset_password');
        Route::post('login', [ExpertController::class, 'login'])
            ->middleware('expert.is_banned')
            ->name('login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [ExpertController::class, 'logout'])->name('logout');
            Route::get('profile', [ExpertController::class, 'profile'])->name('profile.show');
            Route::post('update_profile', [ExpertController::class, 'updateProfile'])->name('profile.update');
            Route::post('change_password', [ExpertController::class, 'changePassword'])->name('password.change');
            Route::delete('delete_account', [ExpertController::class, 'deleteAccount'])->name('account.delete');
        });
    });
});

Route::prefix('expert')->middleware(['auth:sanctum', 'expert.is_active'])->group(function () {
    Route::get('getMyPosts', [PostController::class, 'getMyPosts'])->name('expert.posts.mine');
    Route::post('createPost', [PostController::class, 'createPost'])->name('expert.posts.store');
    Route::get('getPostDetails/{post}', [PostController::class, 'getPostDetails'])->name('expert.posts.show');
    Route::post('updatePost/{post}', [PostController::class, 'updatePost'])->name('expert.posts.update');
    Route::delete('deletePost/{post}', [PostController::class, 'deletePost'])->name('expert.posts.delete');

    Route::get('getMyStories', [StoryController::class, 'getMyStories'])->name('expert.stories.mine');
    Route::post('createStory', [StoryController::class, 'createStory'])->name('expert.stories.store');
    Route::delete('deleteStory/{story}', [StoryController::class, 'deleteStory'])->name('expert.stories.delete');
});
<?php

use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Expert\BookingController;
use App\Http\Controllers\Expert\CalendarController;
use App\Http\Controllers\Expert\ExpertController;
use App\Http\Controllers\Expert\InventoryController;
use App\Http\Controllers\Expert\PostController;
use App\Http\Controllers\Expert\ServiceMaterialController;
use App\Http\Controllers\Expert\StoryController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ==================== Customer ====================
Route::prefix('customer')->name('customer.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('guest_login', [CustomerController::class, 'guestLogin'])->name('guest_login');
        Route::post('register', [CustomerController::class, 'register'])->name('register');
        Route::post('login', [CustomerController::class, 'login'])->name('login');
        Route::post('forgot_password', [CustomerController::class, 'forgotPassword'])->name('forgot_password');
        Route::post('verify_otp', [CustomerController::class, 'verifyOtp'])->name('verify_otp');
        Route::post('reset_password', [CustomerController::class, 'resetPassword'])->name('reset_password');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [CustomerController::class, 'logout'])->name('logout');
            Route::get('profile', [CustomerController::class, 'showProfile'])->name('profile.show');
            Route::post('update_profile', [CustomerController::class, 'updateProfile'])->name('profile.update');
            Route::get('medical_record', [CustomerController::class, 'showMedicalRecord'])->name('medical_record.show');
            Route::post('medical_record', [CustomerController::class, 'updateMedicalRecord'])->name('medical_record.update');
            Route::get('addresses', [CustomerController::class, 'listAddresses'])->name('addresses.index');
            Route::post('addresses', [CustomerController::class, 'storeAddress'])->name('addresses.store');
        });
    });

    Route::get('selected-service-state', [CustomerController::class, 'selectedServiceState'])->name('selected_service_state');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('home', [CustomerController::class, 'home'])->name('home');

        Route::get('providers/top-rated/salons', [CustomerController::class, 'topRatedSalons'])->name('providers.top_rated.salons');
        Route::get('providers/top-rated/beauty-centers', [CustomerController::class, 'topRatedBeautyCenters'])->name('providers.top_rated.beauty_centers');
        Route::get('providers/top-rated/experts', [CustomerController::class, 'topRatedExperts'])->name('providers.top_rated.experts');

        Route::get('providers/search', [CustomerController::class, 'searchProviders'])->name('providers.search');
        Route::get('providers/{type}/{id}', [CustomerController::class, 'showProvider'])->name('providers.show');
        Route::get('providers/{type}/{id}/posts', [CustomerController::class, 'providerPosts'])->name('providers.posts');

        Route::get('feed/posts', [CustomerController::class, 'feedPosts'])->name('feed.posts');

        Route::get('providers/filter/nearby', [CustomerController::class, 'filterNearby'])->name('providers.filter.nearby');
        Route::get('providers/filter/governorate', [CustomerController::class, 'filterByGovernorate'])->name('providers.filter.governorate');
        Route::get('providers/filter/city', [CustomerController::class, 'filterByCity'])->name('providers.filter.city');
        Route::get('providers/filter/service-type', [CustomerController::class, 'filterByServiceType'])->name('providers.filter.service_type');
        Route::get('providers/filter/price', [CustomerController::class, 'filterByPrice'])->name('providers.filter.price');

        Route::get('providers/{type}/{id}/employees', [CustomerController::class, 'providerEmployees'])->name('providers.employees');
        Route::get('providers/{type}/{id}/services', [CustomerController::class, 'providerServices'])->name('providers.services');
        Route::get('services/{id}', [CustomerController::class, 'serviceDetails'])->name('services.show');
        Route::get('services/{id}/questions', [CustomerController::class, 'serviceQuestions'])->name('services.questions');

        Route::get('providers/{type}/{id}/schedule', [CustomerController::class, 'providerSchedule'])->name('providers.schedule');
        Route::get('providers/{type}/{id}/employees/{employeeId}/schedule', [CustomerController::class, 'employeeSchedule'])->name('providers.employee_schedule');

        Route::get('posts/{id}', [CustomerController::class, 'postDetails'])->name('posts.show');
        Route::get('posts/{id}/comments', [CustomerController::class, 'postComments'])->name('posts.comments.index');
        Route::post('posts/{id}/comments', [CustomerController::class, 'addPostComment'])->name('posts.comments.store');
        Route::post('posts/{id}/comments/{commentId}/reply', [CustomerController::class, 'replyToComment'])->name('posts.comments.reply');
        Route::post('posts/{id}/like', [CustomerController::class, 'toggleLikePost'])->name('posts.like');
        Route::post('posts/{id}/favorite', [CustomerController::class, 'toggleFavoritePost'])->name('posts.favorite');

        Route::post('providers/{type}/{id}/follow', [CustomerController::class, 'toggleFollowProvider'])->name('providers.follow');
        Route::post('providers/{type}/{id}/block', [CustomerController::class, 'toggleBlockProvider'])->name('providers.block');
        Route::post('users/{id}/block', [CustomerController::class, 'toggleBlockUser'])->name('users.block');

        Route::post('bookings', [CustomerController::class, 'storeBooking'])->name('bookings.store');
        Route::get('bookings/{id}', [CustomerController::class, 'showBooking'])->name('bookings.show');
        Route::get('bookings/{id}/status', [CustomerController::class, 'bookingStatus'])->name('bookings.status');
        Route::post('bookings/{id}/cancel', [CustomerController::class, 'cancelBooking'])->name('bookings.cancel');
        Route::post('bookings/{id}/reschedule', [CustomerController::class, 'rescheduleBooking'])->name('bookings.reschedule');
        Route::post('bookings/{id}/rate', [CustomerController::class, 'rateBooking'])->name('bookings.rate');
        Route::post('bookings/{id}/report', [CustomerController::class, 'reportBooking'])->name('bookings.report');
        Route::get('bookings/{id}/invoice', [CustomerController::class, 'bookingInvoice'])->name('bookings.invoice');
        Route::get('bookings/history', [CustomerController::class, 'bookingHistory'])->name('bookings.history');
        Route::get('bookings/upcoming', [CustomerController::class, 'upcomingBookings'])->name('bookings.upcoming');
        Route::get('bookings/{id}/details', [CustomerController::class, 'bookingDetails'])->name('bookings.details');
        Route::get('bookings/{id}/services', [CustomerController::class, 'bookingServices'])->name('bookings.services');
        Route::get('bookings/{id}/employee', [CustomerController::class, 'bookingEmployee'])->name('bookings.employee');
        Route::post('bookings/preview', [CustomerController::class, 'previewBooking'])->name('bookings.preview');
        Route::post('bookings/service-selection', [CustomerController::class, 'storeServiceSelection'])->name('bookings.service_selection');
        Route::post('bookings/answers', [CustomerController::class, 'storeBookingAnswers'])->name('bookings.answers');
        Route::get('bookings/summary', [CustomerController::class, 'bookingSummary'])->name('bookings.summary');

        Route::get('notifications', [CustomerController::class, 'notifications'])->name('notifications.index');
        Route::post('notifications/{id}/read', [CustomerController::class, 'markNotificationRead'])->name('notifications.read');

        Route::get('wallet', [CustomerController::class, 'wallet'])->name('wallet.show');
        Route::post('wallet/withdraw', [CustomerController::class, 'withdrawFromWallet'])->name('wallet.withdraw');
        Route::get('wallet/transactions', [CustomerController::class, 'walletTransactions'])->name('wallet.transactions');
    });
});

// ==================== Expert - Auth ====================
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

// ==================== Expert - Posts / Stories ====================
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

// ==================== Expert - Services ====================
Route::prefix('expert')->middleware(['auth:sanctum', 'expert.is_active'])->group(function () {

    // Services CRUD
    Route::get('services', [ServiceController::class, 'index'])->name('expert.services.index');
    Route::post('services', [ServiceController::class, 'store'])->name('expert.services.store');
    Route::post('services/{service}', [ServiceController::class, 'update'])->name('expert.services.update');
    Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('expert.services.destroy');

    // Service Instructions
    Route::post('services/{service}/instructions', [ServiceController::class, 'updateInstructions'])->name('expert.services.instructions');

    // Pre-Booking Questions
    Route::post('services/{service}/questions', [ServiceController::class, 'setQuestions'])->name('expert.services.questions');

    // Min Bookings Remote
    Route::post('services/{service}/min-bookings', [ServiceController::class, 'updateMinBookings'])->name('expert.services.min_bookings');
});

// ==================== Expert - Bookings ====================
Route::prefix('expert')->middleware(['auth:sanctum', 'expert.is_active'])->group(function () {
    Route::get('bookings', [BookingController::class, 'index'])->name('expert.bookings.index');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('expert.bookings.show');
    Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('expert.bookings.cancel');
});

// ==================== Expert - Calendar ====================
Route::prefix('expert')->middleware(['auth:sanctum', 'expert.is_active'])->group(function () {
    Route::get('calendar', [CalendarController::class, 'index'])->name('expert.calendar.index');
    Route::put('calendar', [CalendarController::class, 'update'])->name('expert.calendar.update');
});

// ==================== Expert - Inventory ====================
Route::prefix('expert')->middleware(['auth:sanctum', 'expert.is_active'])->group(function () {

    Route::prefix('inventory')->name('expert.inventory.')->group(function () {

        // مهم: هدول التنتين لازم يكونوا قبل /{item} حتى ما ياكلهن الراوت الديناميكي
        Route::get('alerts', [InventoryController::class, 'alerts'])->name('alerts');
        Route::get('smart-alert', [InventoryController::class, 'smartAlert'])->name('smart_alert');

        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('{item}', [InventoryController::class, 'show'])->name('show');

        // POST كمان مسموح لأنو رفع الصورة بـ multipart ما يمشي مع PUT بكل الكلاينتس
        Route::match(['put', 'post'], '{item}/update', [InventoryController::class, 'update'])->name('update');
        Route::put('{item}', [InventoryController::class, 'update']);

        Route::delete('{item}', [InventoryController::class, 'destroy'])->name('destroy');
        Route::post('{item}/restore', [InventoryController::class, 'restore'])->name('restore');

        // حركات المخزون (إدخال كمية مشتراة / خصم استهلاك / تسوية جرد)
        Route::get('{item}/movements', [InventoryController::class, 'movements'])->name('movements.index');
        Route::post('{item}/movements', [InventoryController::class, 'storeMovement'])->name('movements.store');
    });

    // ربط الخدمة بالمواد يلي تستهلكها
    Route::get('services/{service}/materials', [ServiceMaterialController::class, 'index'])
        ->name('expert.services.materials.index');
    Route::post('services/{service}/materials', [ServiceMaterialController::class, 'sync'])
        ->name('expert.services.materials.sync');
});
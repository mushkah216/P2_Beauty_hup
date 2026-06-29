<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CustomerController;

Route::prefix('v1')->group(function () {
//1-> 7 authentication all
    Route::post('/auth/guest-login', [CustomerController::class, 'guestLogin']);
    Route::post('/auth/register', [CustomerController::class, 'register']);
    Route::post('/auth/login', [CustomerController::class, 'login']);
    Route::post('/auth/forgot-password', [CustomerController::class, 'forgotPassword']);
    Route::post('/auth/verify-otp', [CustomerController::class, 'verifyOtp']);
    Route::post('/auth/reset-password', [CustomerController::class, 'resetPassword']);
    Route::post('/auth/logout', [CustomerController::class, 'logout']);

    //8-> 15 home page
    Route::get('/home', [CustomerController::class, 'home']);
    Route::get('/home/top-rated/salons', [CustomerController::class, 'topRatedSalons']);
    Route::get('/home/top-rated/beauty-centers', [CustomerController::class, 'topRatedBeautyCenters']);
    Route::get('/home/top-rated/experts', [CustomerController::class, 'topRatedExperts']);
    Route::get('/providers/search', [CustomerController::class, 'searchProviders']);
    Route::get('/providers/{type}/{id}', [CustomerController::class, 'showProvider']);
    Route::get('/providers/{type}/{id}/posts', [CustomerController::class, 'providerPosts']);
    Route::get('/feed/posts', [CustomerController::class, 'feedPosts']);

    //16-> 20 flters
    Route::get('/providers/filter/nearby', [CustomerController::class, 'filterNearby']);
    Route::get('/providers/filter/governorate', [CustomerController::class, 'filterByGovernorate']);
    Route::get('/providers/filter/city', [CustomerController::class, 'filterByCity']);
    Route::get('/providers/filter/service-type', [CustomerController::class, 'filterByServiceType']);
    Route::get('/providers/filter/price', [CustomerController::class, 'filterByPrice']);

    //21-> 26 services and employees
    Route::get('/providers/{type}/{id}/employees', [CustomerController::class, 'providerEmployees']);
    Route::get('/providers/{type}/{id}/services', [CustomerController::class, 'providerServices']);
    Route::get('/services/{id}', [CustomerController::class, 'serviceDetails']);
    Route::get('/services/{id}/pre-booking-questions', [CustomerController::class, 'serviceQuestions']);
    Route::get('/providers/{type}/{id}/schedule', [CustomerController::class, 'providerSchedule']);
    Route::get('/providers/{type}/{id}/schedule/{employeeId}', [CustomerController::class, 'employeeSchedule']);

    //27-> 35 posts and providers interactions
    Route::get('/posts/{id}', [CustomerController::class, 'postDetails']);
    Route::get('/posts/{id}/comments', [CustomerController::class, 'postComments']);
    Route::post('/posts/{id}/comments', [CustomerController::class, 'addPostComment']);
    Route::post('/posts/{id}/comments/{commentId}/reply', [CustomerController::class, 'replyToComment']);
    Route::post('/posts/{id}/like', [CustomerController::class, 'toggleLikePost']);
    Route::post('/posts/{id}/favorite', [CustomerController::class, 'toggleFavoritePost']);
    Route::post('/providers/{type}/{id}/follow', [CustomerController::class, 'toggleFollowProvider']);
    Route::post('/providers/{type}/{id}/block', [CustomerController::class, 'toggleBlockProvider']);
    Route::post('/users/{id}/block', [CustomerController::class, 'toggleBlockUser']);

    //36-> 53 booking
    Route::post('/booking', [CustomerController::class, 'storeBooking']);
    Route::get('/booking/{id}', [CustomerController::class, 'showBooking']);
    Route::get('/booking/{id}/status', [CustomerController::class, 'bookingStatus']);
    Route::post('/booking/{id}/cancel', [CustomerController::class, 'cancelBooking']);
    Route::post('/booking/{id}/reschedule', [CustomerController::class, 'rescheduleBooking']);
    Route::post('/booking/{id}/rate', [CustomerController::class, 'rateBooking']);
    Route::post('/booking/{id}/report', [CustomerController::class, 'reportBooking']);
    Route::get('/booking/{id}/invoice', [CustomerController::class, 'bookingInvoice']);
    Route::get('/booking/history', [CustomerController::class, 'bookingHistory']);
    Route::get('/booking/upcoming', [CustomerController::class, 'upcomingBookings']);
    Route::get('/booking/{id}/details', [CustomerController::class, 'bookingDetails']);
    Route::get('/booking/{id}/services', [CustomerController::class, 'bookingServices']);
    Route::get('/booking/{id}/employee', [CustomerController::class, 'bookingEmployee']);
    Route::post('/booking/preview', [CustomerController::class, 'previewBooking']);
    Route::post('/booking/selection', [CustomerController::class, 'storeServiceSelection']);
    Route::post('/booking/questions', [CustomerController::class, 'storeBookingAnswers']);
    Route::post('/booking/summary', [CustomerController::class, 'bookingSummary']);
    Route::get('/booking/selected', [CustomerController::class, 'selectedServiceState']);

    //54->55 notifications
    Route::get('/notifications', [CustomerController::class, 'notifications']);
    Route::patch('/notifications/{id}/read', [CustomerController::class, 'markNotificationRead']);

    //56->65 profile and wallet
    Route::get('/wallet', [CustomerController::class, 'wallet']);
    Route::post('/wallet/withdraw', [CustomerController::class, 'withdrawFromWallet']);
    Route::get('/wallet/transactions', [CustomerController::class, 'walletTransactions']);
    Route::get('/profile', [CustomerController::class, 'showProfile']);
    Route::put('/profile', [CustomerController::class, 'updateProfile']);
    Route::get('/medical-record', [CustomerController::class, 'showMedicalRecord']);
    Route::put('/medical-record', [CustomerController::class, 'updateMedicalRecord']);
    Route::get('/addresses', [CustomerController::class, 'listAddresses']);
    Route::post('/addresses', [CustomerController::class, 'storeAddress']);
});

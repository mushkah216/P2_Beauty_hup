<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    private array $providerTables = [
        'salon' => 'salons',
        'beauty_center' => 'beauty_centers',
        'expert' => 'experts',
    ];

    private function ok(string $message, mixed $data = null, int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function fail(string $message, array $errors = [], int $status = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => (object) $errors,
        ], $status);
    }

    private function tokenFrom(Request $request): ?string
    {
        $header = $request->header('Authorization', '');
        if (!Str::startsWith($header, 'Bearer ')) {
            return null;
        }

        return trim(Str::after($header, 'Bearer '));
    }

    private function authUser(Request $request)
    {
        $token = $this->tokenFrom($request);
        if (!$token) {
            return null;
        }

        return DB::table('users')->where('remember_token', $token)->first();
    }

    private function requireUser(Request $request)
    {
        $user = $this->authUser($request);

        if (!$user) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401));
        }

        return $user;
    }

    private function providerTable(string $type): ?string
    {
        return $this->providerTables[$type] ?? null;
    }

    private function providerOrFail(string $type, int $id)
    {
        $table = $this->providerTable($type);

        if (!$table) {
            return null;
        }

        return DB::table($table)->where('id', $id)->first();
    }

    public function guestLogin()
    {
        return $this->ok('Guest mode enabled', [
            'mode' => 'guest',
            'permissions' => [
                'home',
                'search',
                'provider_view',
                'service_view',
            ],
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $otp, 'created_at' => now()]
        );

        $userId = DB::table('users')->insertGetId([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(80),
            'loyalty_points' => 0,
            'notifications_enabled' => true,
            'language' => 'ar',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->ok('Account created. OTP generated.', [
            'user_id' => $userId,
            'email' => $request->email,
            'otp' => $otp,
            'token' => DB::table('users')->where('id', $userId)->value('remember_token'),
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        $user = DB::table('users')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->fail('Invalid credentials', [], 401);
        }

        $token = Str::random(80);

        DB::table('users')->where('id', $user->id)->update([
            'remember_token' => $token,
            'last_login_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->ok('Logged in successfully', [
            'token' => $token,
            'user' => DB::table('users')->where('id', $user->id)->first(),
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $otp, 'created_at' => now()]
        );

        return $this->ok('OTP generated', [
            'email' => $request->email,
            'otp' => $otp,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        $row = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$row || $row->token !== $request->otp) {
            return $this->fail('Invalid OTP', [], 422);
        }

        return $this->ok('OTP verified successfully');
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        $row = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$row || $row->token !== $request->otp) {
            return $this->fail('Invalid OTP', [], 422);
        }

        DB::table('users')->where('email', $request->email)->update([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(80),
            'updated_at' => now(),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return $this->ok('Password reset successfully');
    }

    public function logout(Request $request)
    {
        $user = $this->authUser($request);

        if ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'remember_token' => null,
                'updated_at' => now(),
            ]);
        }

        return $this->ok('Logged out successfully');
    }

    public function home()
    {
        return $this->ok('Home loaded', [
            'salons' => DB::table('salons')
                ->select('id', 'name', 'profile_photo', 'city', 'governorate', 'rating_count', 'followers_count')
                ->orderByDesc('rating_count')
                ->limit(10)
                ->get(),
            'beauty_centers' => DB::table('beauty_centers')
                ->select('id', 'name', 'profile_photo', 'city', 'governorate', 'rating_count', 'followers_count')
                ->orderByDesc('rating_count')
                ->limit(10)
                ->get(),
            'experts' => DB::table('experts')
                ->select('id', 'full_name as name', 'profile_photo', 'city', 'governorate', 'rating_count', 'followers_count')
                ->orderByDesc('rating_count')
                ->limit(10)
                ->get(),
            'posts' => DB::table('posts')
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
            'stories' => DB::table('stories')
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function topRatedSalons()
    {
        return $this->ok('Top salons loaded', DB::table('salons')
            ->orderByDesc('rating_count')
            ->limit(10)
            ->get());
    }

    public function topRatedBeautyCenters()
    {
        return $this->ok('Top beauty centers loaded', DB::table('beauty_centers')
            ->orderByDesc('rating_count')
            ->limit(10)
            ->get());
    }

    public function topRatedExperts()
    {
        return $this->ok('Top experts loaded', DB::table('experts')
            ->orderByDesc('rating_count')
            ->limit(10)
            ->get());
    }

    public function searchProviders(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        return $this->ok('Search results', [
            'salons' => DB::table('salons')
                ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%"))
                ->limit(20)
                ->get(),
            'beauty_centers' => DB::table('beauty_centers')
                ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%"))
                ->limit(20)
                ->get(),
            'experts' => DB::table('experts')
                ->when($q, fn ($query) => $query->where('full_name', 'like', "%{$q}%"))
                ->limit(20)
                ->get(),
        ]);
    }

    public function showProvider(string $type, int $id)
    {
        $table = $this->providerTable($type);

        if (!$table) {
            return $this->fail('Provider not found', [], 404);
        }

        $provider = DB::table($table)->where('id', $id)->first();

        if (!$provider) {
            return $this->fail('Provider not found', [], 404);
        }

        return $this->ok('Provider details loaded', $provider);
    }

    public function providerPosts(string $type, int $id)
    {
        return $this->ok('Provider posts loaded', DB::table('posts')
            ->where('provider_type', $type)
            ->where('provider_id', $id)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get());
    }

    public function feedPosts()
    {
        return $this->ok('Feed loaded', DB::table('posts')
            ->where('is_active', true)
            ->orderByDesc('likes_count')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get());
    }

    public function filterNearby(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        return $this->ok('Nearby providers', [
            'salons' => DB::table('salons')
                ->select('id', 'name', 'city', 'governorate', 'location_lat', 'location_lng')
                ->when($lat !== null && $lng !== null, fn ($q) => $q->orderByRaw('ABS(location_lat - ?) + ABS(location_lng - ?) ASC', [$lat, $lng]))
                ->limit(20)
                ->get(),
            'beauty_centers' => DB::table('beauty_centers')
                ->select('id', 'name', 'city', 'governorate', 'location_lat', 'location_lng')
                ->when($lat !== null && $lng !== null, fn ($q) => $q->orderByRaw('ABS(location_lat - ?) + ABS(location_lng - ?) ASC', [$lat, $lng]))
                ->limit(20)
                ->get(),
            'experts' => DB::table('experts')
                ->select('id', 'full_name as name', 'city', 'governorate', 'location_lat', 'location_lng')
                ->when($lat !== null && $lng !== null, fn ($q) => $q->orderByRaw('ABS(location_lat - ?) + ABS(location_lng - ?) ASC', [$lat, $lng]))
                ->limit(20)
                ->get(),
        ]);
    }

    public function filterByGovernorate(Request $request)
    {
        $governorate = $request->query('governorate');

        return $this->ok('Governorate filter', [
            'salons' => DB::table('salons')->where('governorate', $governorate)->get(),
            'beauty_centers' => DB::table('beauty_centers')->where('governorate', $governorate)->get(),
            'experts' => DB::table('experts')->where('governorate', $governorate)->get(),
        ]);
    }

    public function filterByCity(Request $request)
    {
        $city = $request->query('city');

        return $this->ok('City filter', [
            'salons' => DB::table('salons')->where('city', $city)->get(),
            'beauty_centers' => DB::table('beauty_centers')->where('city', $city)->get(),
            'experts' => DB::table('experts')->where('city', $city)->get(),
        ]);
    }

    public function filterByServiceType(Request $request)
    {
        $serviceType = trim((string) $request->query('service_type', ''));

        return $this->ok('Service type filter', [
            'services' => DB::table('services')
                ->when($serviceType, fn ($query) => $query->where('name', 'like', "%{$serviceType}%"))
                ->where('is_active', true)
                ->limit(50)
                ->get(),
            'providers' => DB::table('services')
                ->when($serviceType, fn ($query) => $query->where('name', 'like', "%{$serviceType}%"))
                ->distinct()
                ->get(['provider_type', 'provider_id']),
        ]);
    }

    public function filterByPrice(Request $request)
    {
        $min = $request->query('min');
        $max = $request->query('max');

        $query = DB::table('services')->where('is_active', true);

        if ($min !== null) {
            $query->where('price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('price', '<=', $max);
        }

        return $this->ok('Price filter', $query->orderBy('price')->get());
    }

    public function providerEmployees(string $type, int $id)
    {
        if (!$this->providerOrFail($type, $id)) {
            return $this->fail('Provider not found', [], 404);
        }

        return $this->ok('Employees loaded', DB::table('employees')
            ->where('provider_type', $type)
            ->where('provider_id', $id)
            ->where('is_active', true)
            ->get());
    }

    public function providerServices(string $type, int $id)
    {
        if (!$this->providerOrFail($type, $id)) {
            return $this->fail('Provider not found', [], 404);
        }

        return $this->ok('Services loaded', DB::table('services')
            ->where('provider_type', $type)
            ->where('provider_id', $id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get());
    }

    public function serviceDetails(int $id)
    {
        $service = DB::table('services')->where('id', $id)->first();

        if (!$service) {
            return $this->fail('Service not found', [], 404);
        }

        return $this->ok('Service details loaded', $service);
    }

    public function serviceQuestions(int $id)
    {
        return $this->ok('Pre-booking questions loaded', DB::table('pre_booking_questions')
            ->where('service_id', $id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get());
    }

    public function providerSchedule(string $type, int $id)
    {
        if (!$this->providerOrFail($type, $id)) {
            return $this->fail('Provider not found', [], 404);
        }

        return $this->ok('Schedule loaded', DB::table('provider_schedule')
            ->where('provider_type', $type)
            ->where('provider_id', $id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get());
    }

    public function employeeSchedule(string $type, int $id, int $employeeId)
    {
        if (!$this->providerOrFail($type, $id)) {
            return $this->fail('Provider not found', [], 404);
        }

        return $this->ok('Employee schedule loaded', DB::table('provider_schedule')
            ->where('provider_type', $type)
            ->where('provider_id', $id)
            ->where('employee_id', $employeeId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get());
    }

    public function toggleLikePost(Request $request, int $id)
    {
        $user = $this->requireUser($request);

        $exists = DB::table('post_likes')
            ->where('post_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($exists) {
            DB::table('post_likes')
                ->where('post_id', $id)
                ->where('user_id', $user->id)
                ->delete();

            DB::table('posts')->where('id', $id)->decrement('likes_count');

            return $this->ok('Post unliked');
        }

        DB::table('post_likes')->insert([
            'post_id' => $id,
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        DB::table('posts')->where('id', $id)->increment('likes_count');

        return $this->ok('Post liked');
    }

    public function toggleFavoritePost(Request $request, int $id)
    {
        $user = $this->requireUser($request);

        $exists = DB::table('post_favorites')
            ->where('post_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($exists) {
            DB::table('post_favorites')
                ->where('post_id', $id)
                ->where('user_id', $user->id)
                ->delete();

            return $this->ok('Post removed from favorites');
        }

        DB::table('post_favorites')->insert([
            'post_id' => $id,
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        return $this->ok('Post added to favorites');
    }

    public function toggleFollowProvider(Request $request, string $type, int $id)
    {
        $user = $this->requireUser($request);

        if (!$this->providerOrFail($type, $id)) {
            return $this->fail('Provider not found', [], 404);
        }

        $exists = DB::table('follows')
            ->where('follower_id', $user->id)
            ->where('followed_type', $type)
            ->where('followed_id', $id)
            ->first();

        if ($exists) {
            DB::table('follows')
                ->where('follower_id', $user->id)
                ->where('followed_type', $type)
                ->where('followed_id', $id)
                ->delete();

            return $this->ok('Unfollowed successfully');
        }

        DB::table('follows')->insert([
            'follower_id' => $user->id,
            'followed_type' => $type,
            'followed_id' => $id,
            'created_at' => now(),
        ]);

        return $this->ok('Followed successfully');
    }

    public function toggleBlockProvider(Request $request, string $type, int $id)
    {
        $user = $this->requireUser($request);

        if (!$this->providerOrFail($type, $id)) {
            return $this->fail('Provider not found', [], 404);
        }

        $exists = DB::table('blocked_users')
            ->where('blocker_id', $user->id)
            ->where('blocked_type', $type)
            ->where('blocked_id', $id)
            ->first();

        if ($exists) {
            DB::table('blocked_users')
                ->where('blocker_id', $user->id)
                ->where('blocked_type', $type)
                ->where('blocked_id', $id)
                ->delete();

            return $this->ok('Provider unblocked');
        }

        DB::table('blocked_users')->insert([
            'blocker_id' => $user->id,
            'blocked_type' => $type,
            'blocked_id' => $id,
            'created_at' => now(),
        ]);

        return $this->ok('Provider blocked');
    }

    public function toggleBlockUser(Request $request, int $id)
    {
        $user = $this->requireUser($request);

        $exists = DB::table('blocked_users')
            ->where('blocker_id', $user->id)
            ->where('blocked_type', 'user')
            ->where('blocked_id', $id)
            ->first();

        if ($exists) {
            DB::table('blocked_users')
                ->where('blocker_id', $user->id)
                ->where('blocked_type', 'user')
                ->where('blocked_id', $id)
                ->delete();

            return $this->ok('User unblocked');
        }

        DB::table('blocked_users')->insert([
            'blocker_id' => $user->id,
            'blocked_type' => 'user',
            'blocked_id' => $id,
            'created_at' => now(),
        ]);

        return $this->ok('User blocked');
    }

    public function previewBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider_type' => ['required', 'in:salon,beauty_center,expert'],
            'provider_id' => ['required', 'integer'],
            'employee_id' => ['nullable', 'integer'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        $services = DB::table('services')->whereIn('id', $request->service_ids)->get();

        $totalPrice = $services->sum('price');
        $totalDuration = $services->sum('duration_minutes');

        return $this->ok('Booking preview', [
            'selected_services' => $services,
            'total_price' => $totalPrice,
            'total_duration_minutes' => $totalDuration,
            'deposit_percent' => 26,
            'deposit_amount' => round($totalPrice * 0.26, 2),
            'remaining_amount' => round($totalPrice * 0.74, 2),
        ]);
    }

    public function storeServiceSelection(Request $request)
    {
        $user = $this->requireUser($request);

        $validator = Validator::make($request->all(), [
            'provider_type' => ['required', 'in:salon,beauty_center,expert'],
            'provider_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
            'employee_id' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        DB::table('booking_selections')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'provider_type' => $request->provider_type,
                'provider_id' => $request->provider_id,
                'service_id' => $request->service_id,
                'employee_id' => $request->employee_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return $this->ok('Service selection saved');
    }

    public function storeBookingAnswers(Request $request)
    {
        $user = $this->requireUser($request);

        $validator = Validator::make($request->all(), [
            'service_id' => ['required', 'integer'],
            'answers' => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        DB::table('booking_pre_answers')->updateOrInsert(
            ['user_id' => $user->id, 'service_id' => $request->service_id],
            [
                'answers_json' => json_encode($request->answers),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return $this->ok('Pre-booking answers saved');
    }

    public function bookingSummary(Request $request)
    {
        $user = $this->requireUser($request);

        $selection = DB::table('booking_selections')
            ->where('user_id', $user->id)
            ->first();

        $answers = null;

        if ($selection) {
            $row = DB::table('booking_pre_answers')
                ->where('user_id', $user->id)
                ->where('service_id', $selection->service_id)
                ->first();

            $answers = $row ? json_decode($row->answers_json, true) : null;
        }

        return $this->ok('Booking summary', [
            'selection' => $selection,
            'answers' => $answers,
        ]);
    }

    public function selectedServiceState(Request $request)
    {
        $user = $this->authUser($request);

        if (!$user) {
            return $this->ok('No session', ['selected' => null]);
        }

        return $this->ok('Selected service state', DB::table('booking_selections')
            ->where('user_id', $user->id)
            ->first());
    }

    public function notifications(Request $request)
    {
        $user = $this->requireUser($request);

        return $this->ok('Notifications loaded', DB::table('notifications')
            ->where('recipient_type', 'user')
            ->where('recipient_id', $user->id)
            ->orderByDesc('created_at')
            ->get());
    }

    public function markNotificationRead(Request $request, int $id)
    {
        $user = $this->requireUser($request);

        $updated = DB::table('notifications')
            ->where('id', $id)
            ->where('recipient_type', 'user')
            ->where('recipient_id', $user->id)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if (!$updated) {
            return $this->fail('Notification not found', [], 404);
        }

        return $this->ok('Notification marked as read');
    }

    public function showProfile(Request $request)
    {
        $user = $this->requireUser($request);

        return $this->ok('Profile loaded', $user);
    }

    public function updateProfile(Request $request)
    {
        $user = $this->requireUser($request);

        $validator = Validator::make($request->all(), [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'governorate' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'profile_photo' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notifications_enabled' => ['sometimes', 'boolean'],
            'language' => ['sometimes', 'in:ar,en'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        DB::table('users')->where('id', $user->id)->update(array_merge(
            $request->only([
                'full_name',
                'phone',
                'birth_date',
                'governorate',
                'city',
                'profile_photo',
                'notifications_enabled',
                'language',
            ]),
            ['updated_at' => now()]
        ));

        return $this->ok('Profile updated successfully', DB::table('users')->where('id', $user->id)->first());
    }

    public function showMedicalRecord(Request $request)
    {
        $user = $this->requireUser($request);

        return $this->ok('Medical record loaded', DB::table('medical_records')
            ->where('user_id', $user->id)
            ->first());
    }

    public function updateMedicalRecord(Request $request)
    {
        $user = $this->requireUser($request);

        $validator = Validator::make($request->all(), [
            'allergies' => ['sometimes', 'nullable', 'string'],
            'skin_type' => ['sometimes', 'nullable', 'in:oily,dry,combination,normal,sensitive'],
            'hair_type' => ['sometimes', 'nullable', 'string', 'max:100'],
            'previous_procedures' => ['sometimes', 'nullable', 'string'],
            'medications' => ['sometimes', 'nullable', 'string'],
            'chronic_conditions' => ['sometimes', 'nullable', 'string'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        DB::table('medical_records')->updateOrInsert(
            ['user_id' => $user->id],
            array_merge(
                $request->only([
                    'allergies',
                    'skin_type',
                    'hair_type',
                    'previous_procedures',
                    'medications',
                    'chronic_conditions',
                    'notes',
                ]),
                [
                    'last_updated_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            )
        );

        return $this->ok('Medical record updated successfully');
    }

    public function listAddresses(Request $request)
    {
        $user = $this->requireUser($request);

        return $this->ok('Addresses loaded', DB::table('addresses')
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get());
    }

    public function storeAddress(Request $request)
    {
        $user = $this->requireUser($request);

        $validator = Validator::make($request->all(), [
            'label' => ['required', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'governorate' => ['required', 'string', 'max:100'],
            'location_lat' => ['nullable', 'numeric'],
            'location_lng' => ['nullable', 'numeric'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        if ($request->boolean('is_default', false)) {
            DB::table('addresses')
                ->where('user_id', $user->id)
                ->update(['is_default' => false]);
        }

        $id = DB::table('addresses')->insertGetId([
            'user_id' => $user->id,
            'label' => $request->label,
            'address_line' => $request->address_line,
            'city' => $request->city,
            'governorate' => $request->governorate,
            'location_lat' => $request->location_lat,
            'location_lng' => $request->location_lng,
            'is_default' => $request->boolean('is_default', false),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->ok('Address stored successfully', [
            'address_id' => $id,
        ], 201);
    }

    public function archiveBookings(Request $request)
    {
        $user = $this->requireUser($request);

        return $this->ok('Archived bookings loaded', DB::table('bookings')
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderByDesc('created_at')
            ->get());
    }

    public function listBookings(Request $request)
    {
        $user = $this->requireUser($request);

        return $this->ok('Bookings loaded', DB::table('bookings')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get());
    }

    public function showBooking(Request $request, int $id)
    {
        $user = $this->requireUser($request);

        $booking = DB::table('bookings')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return $this->fail('Booking not found', [], 404);
        }

        return $this->ok('Booking details loaded', [
            'booking' => $booking,
            'services' => DB::table('booking_services')->where('booking_id', $id)->get(),
            'answers' => DB::table('booking_question_answers')->where('booking_id', $id)->get(),
            'review' => DB::table('reviews')->where('booking_id', $id)->first(),
        ]);
    }

    public function createBooking(Request $request)
    {
        $user = $this->requireUser($request);

        $validator = Validator::make($request->all(), [
            'provider_type' => ['required', 'in:expert,salon,beauty_center'],
            'provider_id' => ['required', 'integer'],
            'employee_id' => ['nullable', 'integer'],
            'booking_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'integer'],
            'services.*.price_snapshot' => ['required', 'numeric'],
            'services.*.duration_minutes' => ['required', 'integer'],
            'total_price' => ['required', 'numeric'],
            'deposit_amount' => ['required', 'numeric'],
            'remaining_amount' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
            'answers' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors()->toArray());
        }

        $bookingId = DB::transaction(function () use ($request, $user) {
            $bookingId = DB::table('bookings')->insertGetId([
                'user_id' => $user->id,
                'provider_type' => $request->provider_type,
                'provider_id' => $request->provider_id,
                'employee_id' => $request->employee_id,
                'booking_date' => $request->booking_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'total_price' => $request->total_price,
                'deposit_amount' => $request->deposit_amount,
                'remaining_amount' => $request->remaining_amount,
                'status' => 'pending',
                'notes' => $request->notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->services as $service) {
                DB::table('booking_services')->insert([
                    'booking_id' => $bookingId,
                    'service_id' => $service['service_id'],
                    'employee_id' => $service['employee_id'] ?? $request->employee_id,
                    'price_snapshot' => $service['price_snapshot'],
                    'duration_minutes' => $service['duration_minutes'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (is_array($request->answers)) {
                foreach ($request->answers as $answer) {
                    DB::table('booking_question_answers')->insert([
                        'booking_id' => $bookingId,
                        'question_id' => $answer['question_id'],
                        'answer_text' => $answer['answer_text'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return $bookingId;
        });

        return $this->ok('Booking created successfully', [
            'booking_id' => $bookingId,
        ], 201);
    }

    public function cancelBooking(Request $request, int $id)
    {
        $user = $this->requireUser($request);

        $booking = DB::table('bookings')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return $this->fail('Booking not found', [], 404);
        }

        DB::table('bookings')->where('id', $id)->update([
            'status' => 'cancelled',
            'cancelled_by' => 'user',
            'cancellation_reason' => $request->input('reason'),
            'updated_at' => now(),
        ]);

        return $this->ok('Booking cancelled successfully');
    }

    public function confirmBookingPayment(Request $request, int $id)
    {
        $user = $this->requireUser($request);

        $booking = DB::table('bookings')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return $this->fail('Booking not found', [], 404);
        }

        $paymentId = DB::table('payments')->insertGetId([
            'user_id' => $user->id,
            'payable_id' => $id,
            'currency' => $request->input('currency', 'USD'),
            'payment_gateway' => $request->input('payment_gateway', 'manual'),
            'gateway_transaction_id' => $request->input('gateway_transaction_id', (string) Str::uuid()),
            'idempotency_key' => $request->input('idempotency_key', (string) Str::uuid()),
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('bookings')->where('id', $id)->update([
            'status' => 'confirmed',
            'updated_at' => now(),
        ]);

        DB::table('notifications')->insert([
            'recipient_type' => 'user',
            'recipient_id' => $user->id,
            'type' => 'payment_confirmed',
            'title' => 'Payment confirmed',
            'body' => 'Your booking payment has been confirmed.',
            'data_json' => json_encode([
                'booking_id' => $id,
                'payment_id' => $paymentId,
            ]),
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->ok('Payment confirmed and booking updated', [
            'payment_id' => $paymentId,
            'booking_id' => $id,
        ]);
    }
}

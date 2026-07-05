<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Expert extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password_hash',
        'otp_code',
    ];

    protected $casts = [
        'experience_years'        => 'integer',
        'location_lat'            => 'decimal:7',
        'location_lng'            => 'decimal:7',
        'service_area_km'         => 'decimal:2',
        'rating_avg'              => 'decimal:2',
        'is_available_for_hire'   => 'boolean',
        'subscription_expires_at' => 'date',
        'birth_date'              => 'date',
        'last_login_at'           => 'datetime',
        'email_verified_at'       => 'datetime',
        'otp_expires_at'          => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    
    public function employmentRequests()
    {
        return $this->hasMany(EmploymentRequest::class, 'expert_id');
    }

    
    public function employeeProfiles()
    {
        return $this->hasMany(Employee::class, 'expert_id');
    }

    public function services()
    {
        return $this->morphMany(Service::class, 'provider');
    }

    public function bookings()
    {
        return $this->morphMany(Booking::class, 'provider');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'provider');
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'provider');
    }

    public function stories()
    {
        return $this->morphMany(Story::class, 'provider');
    }

    public function offers()
    {
        return $this->morphMany(Offer::class, 'provider');
    }

    public function courses()
    {
        return $this->morphMany(Course::class, 'provider');
    }

    public function certificates()
    {
        return $this->morphMany(Certificate::class, 'provider');
    }

    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'provider');
    }

    public function schedules()
    {
        return $this->morphMany(Schedule::class, 'entity');
    }

    public function followers()
    {
        return $this->morphMany(Follow::class, 'followed');
    }

    public function blockedUsers()
    {
        return $this->morphMany(Block::class, 'blocker');
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'recipient');
    }

    public function complaints()
    {
        return $this->morphMany(Complaint::class, 'submitted_by');
    }

    public function chats()
    {
        return $this->morphMany(Chat::class, 'p1');
    }

    
    public function hasChatWith(string $userType, int $userId)
    {
        return $this->chats()
            ->where('p2_type', $userType)
            ->where('p2_id', $userId)
            ->first();
    }

    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->account_status === 'suspended';
    }

    public function isVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }
}
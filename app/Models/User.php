<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'profile_photo',
        'birth_date',
        'gender',
        'governorate',
        'city',
        'location_lat',
        'location_lng',
        'loyalty_points',
        'otp_code',
        'otp_expires_at',
        'password',
        'is_active',
        'notifications_enabled',
            'email_verified_at',

    'otp_code',

    'otp_expires_at',

        'language',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'location_lat' => 'decimal:7',
            'location_lng' => 'decimal:7',
            'loyalty_points' => 'integer',
            'is_active' => 'boolean',
            'notifications_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function loyaltyPointsTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyPointsTransaction::class);
    }

    public function providerFavorites(): HasMany
    {
        return $this->hasMany(ProviderFavorite::class);
    }

    public function medicalRecordAccesses(): HasMany
    {
        return $this->hasMany(MedicalRecordAccess::class);
    }

    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'recipient');
    }

    public function complaints(): MorphMany
    {
        return $this->morphMany(Complaint::class, 'submitted_by');
    }

    public function chatsAsFirstParticipant(): HasMany
    {
        return $this->hasMany(Chat::class, 'p1_id')
            ->where('p1_type', 'user');
    }

    public function chatsAsSecondParticipant(): HasMany
    {
        return $this->hasMany(Chat::class, 'p2_id')
            ->where('p2_type', 'user');
    }

    public function messagesSent(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id')
            ->where('sender_type', 'user');
    }

    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class, 'enrollee_id')
            ->where('enrollee_type', 'user');
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function canUseNotifications(): bool
    {
        return (bool) $this->notifications_enabled;
    }
}
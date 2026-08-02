<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Warehouse extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'otp_code',
    ];

    protected $casts = [
        'location_lat'      => 'decimal:7',
        'location_lng'      => 'decimal:7',
        'is_active'         => 'boolean',
        'is_banned'         => 'boolean',
        'email_verified_at' => 'datetime',
        'otp_expires_at'    => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
    ];

    /** منتجات المخزن */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'provider_id')
            ->where('provider_type', Product::PROVIDER_WAREHOUSE);
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
        return ! is_null($this->email_verified_at);
    }
}
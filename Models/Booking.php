<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_type',
        'provider_id',
        'employee_id',
        'service_id',
        'booking_date',
        'booking_time',
        'status',
        'subtotal',
        'discount',
        'deposit_amount',
        'total_amount',
        'notes',
        'canceled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'canceled_at' => 'datetime',
            'completed_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(BookingAnswer::class);
    }
}
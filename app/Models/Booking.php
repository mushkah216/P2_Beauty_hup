<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'booking_date'     => 'date',
        'total_price'      => 'decimal:2',
        'deposit_amount'   => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'auto_cancel_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function bookingServices()
    {
        return $this->hasMany(BookingService::class);
    }

    // ربط يدوي بدل morphTo، لأن payable_type بجدول payments قيمته نص بسيط مش اسم كلاس كامل
    public function payment()
    {
        return $this->hasOne(Payment::class, 'payable_id')->where('payable_type', 'booking');
    }
}

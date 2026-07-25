<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price'                     => 'decimal:2',
        'deposit_percent'           => 'decimal:2',
        'duration_minutes'          => 'integer',
        'min_bookings_remote'       => 'integer',
        'cancellation_deadline_hrs' => 'integer',
        'is_active'                 => 'boolean',
    ];

     
    public function provider()
    {
        return $this->morphTo();
    }

    // الخدمة تنتمي لتصنيف
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    // أسئلة ما قبل الحجز الخاصة بهذه الخدمة
    public function preBookingQuestions()
    {
        return $this->hasMany(PreBookingQuestion::class, 'service_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingService extends Model
{
    //
    public $timestamps = false;

    protected $guarded = [];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

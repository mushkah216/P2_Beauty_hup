<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreBookingQuestion extends Model
{
    //
     protected $guarded = [];

    protected $casts = [
        'options_json' => 'array',
        'is_required'  => 'boolean',
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function provider()
    {
        return $this->morphTo();
    }
}

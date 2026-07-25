<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderSchedule extends Model
{
    //
    protected $table = 'provider_schedule';

    protected $guarded = [];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_active'   => 'boolean',
    ];
}

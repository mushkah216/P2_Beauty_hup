<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceMaterial extends Model
{
    //
    protected $table = 'service_materials';

    protected $fillable = [
        'service_id',
        'product_id',
        'quantity_per_session',
        'unit',
    ];

    protected $casts = [
        'quantity_per_session' => 'decimal:2',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

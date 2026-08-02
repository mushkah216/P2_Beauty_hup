<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    //
    public $timestamps = false; // بالـ ERD في created_at بس

    /** movement_type */
    public const TYPE_IN         = 'in';
    public const TYPE_OUT        = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    /** reason — مطابقة للـ enum بالداتابيز */
    public const REASON_PURCHASE    = 'purchase';      // شراء مواد
    public const REASON_SALE        = 'sale';          // بيع منتج
    public const REASON_CONSUMPTION = 'booking_used';  // استهلاك بخدمة
    public const REASON_RETURN      = 'return';        // إرجاع
    public const REASON_STOCKTAKE   = 'adjustment';    // تسوية جرد
    public const REASON_EXPIRED     = 'expired';       // تلف / انتهاء صلاحية

    protected $fillable = [
        'product_id',
        'movement_type',
        'reason',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by_type',
        'created_by_id',
        'created_at',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /** الإشارة يلي تتطبق على المخزون حسب نوع الحركة */
    public function signedQuantity(): float
    {
        return $this->movement_type === self::TYPE_OUT
            ? -1 * (float) $this->quantity
            : (float) $this->quantity;
    }
}

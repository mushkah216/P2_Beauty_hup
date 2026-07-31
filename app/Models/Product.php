<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Product extends Model
{
    //
   public const PROVIDER_EXPERT = 'expert';
    public const PROVIDER_SALON  = 'salon';
    public const PROVIDER_CENTER = 'center';

    protected $fillable = [
        'provider_type',
        'provider_id',
        'category_id',
        'name',
        'description',
        'sku',
        'price',
        'wholesale_price',
        'stock_quantity',
        'min_stock_threshold',
        'main_image',
        'images_json',
        'weight_grams',
        'is_active',
        'is_archived',
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'wholesale_price'     => 'decimal:2',
        'stock_quantity'      => 'decimal:2',
        'min_stock_threshold' => 'decimal:2',
        'weight_grams'        => 'integer',
        'images_json'         => 'array',
        'is_active'           => 'boolean',
        'is_archived'         => 'boolean',
    ];

    public function provider(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'provider_type', 'provider_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function serviceMaterials(): HasMany
    {
        return $this->hasMany(ServiceMaterial::class, 'product_id');
    }

    /** كل مواد مزود معيّن */
    public function scopeForProvider(Builder $query, string $type, int|string $id): Builder
    {
        return $query->where('provider_type', $type)->where('provider_id', $id);
    }

    /** المواد يلي وصلت أو نزلت تحت الحد الأدنى */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereNotNull('min_stock_threshold')
            ->whereColumn('stock_quantity', '<=', 'min_stock_threshold');
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->min_stock_threshold !== null
            && (float) $this->stock_quantity <= (float) $this->min_stock_threshold;
    }
}

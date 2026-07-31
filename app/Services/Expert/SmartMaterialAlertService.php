<?php

namespace App\Services\Expert;

use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * الإشعار الذكي: شو المواد المطلوبة لتنفيذ حجوزات يوم معيّن (الافتراضي بكرا)،
 * وهل الموجود بالمخزون يكفي أو لأ.
 *
 * المنطق: حجوزات اليوم المطلوب -> الخدمات المحجوزة فيها -> المواد يلي تستهلكها
 * كل خدمة (service_materials) -> جمع الكميات لكل مادة -> مقارنتها مع stock_quantity.
 */
class SmartMaterialAlertService
{
    /**
     * حالات الحجز يلي منعتبرها "رح تنفَّذ".
     * completed معناها الخدمة انعملت والمواد انصرفت، و cancelled ملغي —
     * فما إلهن علاقة بمواد بكرا.
     */
    public const ACTIVE_BOOKING_STATUSES = ['pending', 'confirmed'];

    public function forDate(int|string $expertId, CarbonInterface $date): array
    {
        $dateString = $date->toDateString();

        $rows = DB::table('bookings')
            ->join('booking_services', 'booking_services.booking_id', '=', 'bookings.id')
            ->join('service_materials', 'service_materials.service_id', '=', 'booking_services.service_id')
            ->join('products', 'products.id', '=', 'service_materials.product_id')
            ->where('bookings.provider_type', Product::PROVIDER_EXPERT)
            ->where('bookings.provider_id', $expertId)
            ->whereDate('bookings.booking_date', $dateString)
            ->whereIn('bookings.status', self::ACTIVE_BOOKING_STATUSES)
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.stock_quantity',
                'products.min_stock_threshold'
            )
            ->selectRaw('
                products.id                          as product_id,
                products.name                        as product_name,
                products.sku                         as sku,
                products.stock_quantity              as stock_quantity,
                products.min_stock_threshold          as min_stock_threshold,
                SUM(service_materials.quantity_per_session) as required_quantity,
                COUNT(DISTINCT bookings.id)          as bookings_count
            ')
            ->get();

        $materials = $rows->map(function ($row) {
            $required  = (float) $row->required_quantity;
            $available = (float) $row->stock_quantity;
            $shortage  = max(0, round($required - $available, 2));

            return [
                'product_id'         => (int) $row->product_id,
                'product_name'       => $row->product_name,
                'sku'                => $row->sku,
                'required_quantity'  => round($required, 2),
                'available_quantity' => round($available, 2),
                'shortage_quantity'  => $shortage,
                'is_enough'          => $shortage === 0.0,
                'bookings_count'     => (int) $row->bookings_count,
            ];
        })
            // النواقص أول، وبعدها الأكبر استهلاكاً
            ->sortByDesc(fn ($m) => [$m['shortage_quantity'] > 0 ? 1 : 0, $m['shortage_quantity']])
            ->values()
            ->all();

        $bookingsCount = DB::table('bookings')
            ->where('provider_type', Product::PROVIDER_EXPERT)
            ->where('provider_id', $expertId)
            ->whereDate('booking_date', $dateString)
            ->whereIn('status', self::ACTIVE_BOOKING_STATUSES)
            ->count();

        return [
            'date'            => $dateString,
            'bookings_count'  => $bookingsCount,
            'materials'       => $materials,
            'shortages_count' => count(array_filter($materials, fn ($m) => ! $m['is_enough'])),
        ];
    }

    /**
     * الكمية الموجودة من مادة معيّنة تكفي كم شخص (جلسة).
     * منرجع الأقل بين الخدمات — يعني الرقم المضمون.
     * بترجع null إذا المادة مش مربوطة بأي خدمة.
     */
    public function sufficientForPersons(Product $product): ?int
    {
        $perSession = DB::table('service_materials')
            ->where('product_id', $product->getKey())
            ->where('quantity_per_session', '>', 0)
            ->max('quantity_per_session');

        if ($perSession === null) {
            return null;
        }

        return (int) floor((float) $product->stock_quantity / (float) $perSession);
    }
}
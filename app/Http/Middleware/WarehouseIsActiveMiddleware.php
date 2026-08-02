<?php

namespace App\Http\Middleware;

use App\Models\Warehouse;
use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WarehouseIsActiveMiddleware
{
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next): Response
    {
        $warehouse = Auth::user();

        // فحص النوع مهم: توكن خبير ممكن يوصل لراوت مخزن
        if (! $warehouse instanceof Warehouse) {
            return $this->sendError('غير مصرح لك بالوصول لهذا القسم.', 403);
        }

        if ($warehouse->is_banned) {
            return $this->sendError('تم حظر حسابك.', 403);
        }

        if ($warehouse->account_status !== 'active') {
            return $this->sendError('حسابك غير مفعّل بعد. يرجى انتظار موافقة الإدارة.', 403);
        }

        return $next($request);
    }
}
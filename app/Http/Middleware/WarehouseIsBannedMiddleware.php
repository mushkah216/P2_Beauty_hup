<?php

namespace App\Http\Middleware;

use App\Models\Warehouse;
use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WarehouseIsBannedMiddleware
{
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next): Response
    {
        $warehouse = Warehouse::where('email', $request->email)->first();

        if ($warehouse && $warehouse->is_banned) {
            return $this->sendError('تم حظر حسابك.', 403);
        }

        return $next($request);
    }
}
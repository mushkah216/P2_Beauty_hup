<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExpertIsActiveMiddleware
{
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next): Response
    {
        $expert = Auth::user();

        if ($expert->account_status !== 'active') {
            return $this->sendError('حسابك غير مفعّل بعد. يرجى انتظار موافقة الإدارة.', 403);
        }

        return $next($request);
    }
}
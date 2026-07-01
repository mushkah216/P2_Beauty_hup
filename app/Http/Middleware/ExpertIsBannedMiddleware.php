<?php

namespace App\Http\Middleware;

use App\Models\Expert;
use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpertIsBannedMiddleware
{
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next): Response
    {
        $expert = Expert::where('email', $request->email)->first();

        if ($expert && $expert->is_banned) {
            return $this->sendError('Your account is banned.', 403);
        }

        return $next($request);
    }
}
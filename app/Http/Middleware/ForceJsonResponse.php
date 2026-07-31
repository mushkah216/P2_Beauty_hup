<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin', '*'));
        $response->headers->set('Vary', 'Origin');

        return $response;
    }
}
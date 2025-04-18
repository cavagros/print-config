<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('Content-Security-Policy', 
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-eval' 'unsafe-inline' https://unpkg.com; " .
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdnjs.cloudflare.com; " .
                "font-src 'self' https://fonts.bunny.net; " .
                "img-src 'self' data:; " .
                "connect-src 'self';"
            );
        }

        return $response;
    }
} 
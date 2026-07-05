<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    




    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $response instanceof \Illuminate\Http\Response ||
            $response instanceof \Illuminate\Http\JsonResponse ||
            $response instanceof \Symfony\Component\HttpFoundation\Response
        ) {
            
            $response->headers->set('X-Frame-Options', 'ALLOWALL');
            $response->headers->set('X-Content-Type-Options', 'nosniff');

            



            $csp = implode(' ', [
                "default-src 'self' * http://* https://* data: blob: 'unsafe-inline' 'unsafe-eval';",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' * http://* https://* data: blob:;",
                "style-src 'self' 'unsafe-inline' * http://* https://* data: blob:;",
                "font-src 'self' data: * http://* https://*;",
                "img-src 'self' data: blob: * http://* https://*;",
                "connect-src 'self' * http://* https://* data: blob: ws: wss:;",
                "frame-src 'self' * http://* https://* data: blob:;",
                "media-src 'self' blob: * http://* https://*;",
                "object-src 'self' * http://* https://* data: blob:;",
                "frame-ancestors * http://* https://*;",
            ]);

            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
}

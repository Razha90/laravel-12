<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfTokenForGet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')) {
            $token = $request->header('X-CSRF-TOKEN') ?? $request->input('_token');

            if (!$token || $token !== csrf_token()) {
                return response()->json(['message' => 'CSRF token mismatch'], Response::HTTP_FORBIDDEN);
            }
        }
        return $next($request);
    }
}

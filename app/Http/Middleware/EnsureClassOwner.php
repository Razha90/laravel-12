<?php

namespace App\Http\Middleware;

use App\Models\Classroom;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureClassOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $classId = $request->route('id'); // Ambil ID dari route
        $classroom = Classroom::find($classId);
        if (!$classroom || $classroom->user_id !== Auth::id()) {
        Log::info('User ' . Auth::id() . ' tried to access class ' . $classId);

            abort(403, 'Anda tidak memiliki izin untuk mengakses kelas ini.');
        }
        return $next($request);
    }
}

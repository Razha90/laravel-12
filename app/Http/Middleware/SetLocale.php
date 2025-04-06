<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $supportedLocales = ['en', 'fr', 'id'];
        // $locale = $request->session()->get('locale');
        // if (!$locale) {
        //     App::setLocale('fr');
        //     Session::put('locale', 'fr');
        //     return $next($request);
        // }
        
        // $getSession = App::getLocale();
        // if (in_array($locale, $supportedLocales)) {
        //     if ($getSession !== $locale) {
        //         App::setLocale($locale);
        //     }
        // } else {
        //     App::setLocale('fr');
        //     Session::put('locale', 'fr');
        // }
        // return $next($request);
        $supportedLocales = ['en', 'fr', 'id'];
        
        // Ambil bahasa dari cookie jika ada, jika tidak gunakan 'fr' sebagai default
        $locale = $request->cookie('locale', 'fr');
        
        // Jika bahasa tidak ada atau tidak valid, set ke default 'fr'
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'fr';
        }

        // Set bahasa aplikasi
        App::setLocale($locale);

        return $next($request);
    }
}

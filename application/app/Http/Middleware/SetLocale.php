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
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } elseif (auth()->check() && auth()->user()->locale) {
            App::setLocale(auth()->user()->locale);
            Session::put('locale', auth()->user()->locale);
        } else {
            App::setLocale(config('app.locale', 'tr'));
        }

        return $next($request);
    }
}

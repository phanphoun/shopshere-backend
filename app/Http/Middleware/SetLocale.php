<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    protected array $supported = ['en', 'km'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('X-App-Locale') ?? $request->query('lang');

        if ($locale && in_array($locale, $this->supported, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}

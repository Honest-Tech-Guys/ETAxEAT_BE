<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the language from the 'Accept-Language' header
        $locale = $request->header('Accept-Language');
        // Check if the locale is supported, otherwise use the default
        if ($locale && in_array($locale, config('voyager.multilingual.locales'))) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}

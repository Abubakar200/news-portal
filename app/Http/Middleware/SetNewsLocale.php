<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetNewsLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        abort_unless(in_array($locale, ['en', 'ur'], true), 404);

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}

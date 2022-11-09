<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ForceSSL
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && !app()->environment('local')) {
            dd('https://' . $request->getHost() . $request->getRequestUri());
            return redirect()->to('https://' . $request->getHost() . $request->getRequestUri(), 302);
        }
        return $next($request);
    }
}
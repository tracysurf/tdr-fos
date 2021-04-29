<?php

namespace App\Http\Middleware;

use Closure;

class PrivateApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->get('token') !== getenv('FOS_API_TOKEN'))
            return response('', 503);

        return $next($request);
    }
}

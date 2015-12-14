<?php

namespace App\Http\Middleware;

use Closure;

class JsonApiMiddleware
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
		if (!empty($request->header('Content-Type')) &&
			$request->header('Content-Type') != 'application/vnd.api+json')
		{
			abort(415, 'Unsupported Media Type');
		}
		else if (!empty($request->header('Accept')) &&
			false === strpos($request->header('Accept'), '*/*') &&
			false === strpos($request->header('Accept'), 'application/vnd.api+json'))
		{
			abort(406, 'Not Acceptable');
		}
        return $next($request);
    }
}

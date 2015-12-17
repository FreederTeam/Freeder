<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response as IlluminateResponse;

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
        // Handle Content-Type
        if (!empty($request->header('Content-Type')) &&
            $request->header('Content-Type') != 'application/vnd.api+json') {
            // Abort with IlluminateResponse::HTTP_UNSUPPORTED_MEDIA_TYPE
            abort(
                IlluminateResponse::HTTP_UNSUPPORTED_MEDIA_TYPE
            );
        } else if (!empty($request->header('Accept')) &&
            false === strpos($request->header('Accept'), '*/*') &&
            false === strpos($request->header('Accept'), 'application/vnd.api+json')) {
            // Abort with IlluminateResponse::HTTP_NOT_ACCEPTABLE
            abort(
                IlluminateResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        // Handle POST data
        if ($request->isMethod('post') && !$request->has('data')) {
            // Abort with IlluminateResponse::HTTP_BAD_REQUEST
            abort(
                IlluminateResponse::HTTP_BAD_REQUEST
            );
        }

        // Continue
        return $next($request);
    }
}

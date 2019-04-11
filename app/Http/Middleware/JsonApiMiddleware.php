<?php

namespace App\Http\Middleware;

use Closure;

class JsonApiMiddleware
{
    const PARSED_METHODS = [
        'POST','GET'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (in_array($request->getMethod(), self::PARSED_METHODS)) {

           /*
            * get json body content, decode it and merge with request contents
            */
            $temp = json_decode($request->getContent(),true);

            $request->merge($temp[0]);
        }

        return $next($request);
    }
}

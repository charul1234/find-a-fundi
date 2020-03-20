<?php

namespace App\Http\Middleware;

use Closure;

class XMLHttpRequest
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
        //header('Access-Control-Allow-Origin: *');
        //header('Access-Control-Allow-Headers: Authorization, Content-Type');
        
        // This we need to from application but we have set from our side
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        return $next($request);
    }
}

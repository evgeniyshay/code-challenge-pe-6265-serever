<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddContentLength
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // to be sure nothing was already output (by an echo statement or something)
        if (headers_sent() || ob_get_contents() != '') {
            return $response;
        }

        $content = $response->content();
         
        $response->header('Content-Length', strlen($content));

        return $response;
    }
}

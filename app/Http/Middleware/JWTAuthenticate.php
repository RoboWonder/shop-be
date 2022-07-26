<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;

class JWTAuthenticate extends \Tymon\JWTAuth\Http\Middleware\Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->checkForToken($request);

        try {
            if (!$this->auth->parseToken()->authenticate()) {
                return response()->json(["success" => FALSE, "message" => "unauthorized_fail"]);
            }
        } catch (JWTException $e) {
            return response()->json(["success" => FALSE, "message" => "unauthorized_fail"]);
        }

        return $next($request);
    }
}

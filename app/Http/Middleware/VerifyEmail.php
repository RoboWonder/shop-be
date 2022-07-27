<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class VerifyEmail extends \Tymon\JWTAuth\Http\Middleware\Authenticate
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
        if (!!Auth::user()->verified) {
            return response()->json(
                [
                    'success' => FALSE,
                    'message' => 'shopbe_must_verify_email',
                    'data' => [
                        'must'  => 'VERIFY_EMAIL'
                    ]
                ],
                401
            );
        }

        return $next($request);
    }
}

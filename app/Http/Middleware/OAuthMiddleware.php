<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class OAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $access_token = $this->getBearerToken($request);

        // Use a dedicated method or service for token validation
        if (!$this->validateAccessToken($access_token)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        return $next($request);
    }

    /**
     * Extracts the Bearer token from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getBearerToken(Request $request): ?string
    {
        $authorizationHeader = $request->header('Authorization');

        if (is_null($authorizationHeader)) {
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Validates the access token.
     *
     * @param  string|null  $token
     * @return bool
     */
    protected function validateAccessToken(?string $token): bool
    {
        return $token === config('jwt.secret');
    }
}

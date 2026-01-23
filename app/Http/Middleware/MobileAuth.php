<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class MobileAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if this is a mobile app request
        $isMobile = $request->hasHeader('User-Agent') && 
                   str_contains($request->userAgent(), 'LioSyncMobile');
        
        if ($isMobile && $request->bearerToken()) {
            // Authenticate using Sanctum token
            $token = PersonalAccessToken::findToken($request->bearerToken());
            
            if ($token && $token->tokenable) {
                // Login the user to establish session
                Auth::login($token->tokenable);
                return $next($request);
            }
        }
        
        // Fall through to normal auth middleware
        return $next($request);
    }
}

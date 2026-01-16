<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Skip check for superadmin
        if ($user && $user->hasRole('Superadmin')) {
            return $next($request);
        }

        // Check if user has a branch with active subscription
        if ($user && $user->branch) {
            $hasActiveSubscription = $user->branch->hasActiveSubscription();

            // Allow GET requests (read-only) even if expired
            if ($request->isMethod('GET') && !$hasActiveSubscription) {
                // Allow read-only access
                return $next($request);
            }

            // Block write operations if subscription is not active
            if (!$hasActiveSubscription && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your subscription has expired. Please renew to continue.',
                        'redirect' => route('subscription.index')
                    ], 403);
                }

                return redirect()->route('subscription.index')
                    ->with('error', 'Your subscription has expired. Please renew to continue.');
            }
        }

        return $next($request);
    }
}

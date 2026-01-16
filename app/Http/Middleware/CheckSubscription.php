<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Branch;

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

        // Get the effective branch to check
        // Priority: active_branch_id (session) > user's branch
        $branchId = session('active_branch_id') ?? $user?->branch_id;

        if ($branchId) {
            $branch = Branch::find($branchId);

            if ($branch) {
                $hasActiveSubscription = $branch->hasActiveSubscription();

                // Set read-only mode flag for views
                if (!$hasActiveSubscription) {
                    session()->put('subscription_readonly', true);
                } else {
                    session()->forget('subscription_readonly');
                }

                // Allow GET requests (read-only) even if expired
                if ($request->isMethod('GET') && !$hasActiveSubscription) {
                    // Allow read-only access
                    return $next($request);
                }

                // Block write operations if subscription is not active
                if (!$hasActiveSubscription && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                    if ($request->expectsJson()) {
                        // For cashiers, don't redirect - just return error
                        if ($user && $user->isCashier()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Maaf, langganan cabang ini telah habis. Silakan hubungi admin untuk perpanjangan.',
                            ], 403);
                        }

                        // For admins, redirect to subscription page
                        return response()->json([
                            'success' => false,
                            'message' => 'Langganan cabang ini telah habis. Silakan perpanjang untuk melanjutkan.',
                            'redirect' => route('subscription.index')
                        ], 403);
                    }

                    // For cashiers, don't redirect - just show error
                    if ($user && $user->isCashier()) {
                        return back()->with('error', 'Maaf, langganan cabang ini telah habis. Silakan hubungi admin untuk perpanjangan.');
                    }

                    // For admins, redirect to subscription page
                    return redirect()->route('subscription.index')
                        ->with('error', 'Langganan cabang ini telah habis. Silakan perpanjang untuk melanjutkan.');
                }
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictCashierAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // If user is not logged in, continue to authentication
        if (!$user) {
            return $next($request);
        }

        // If user is a cashier, they can only access POS routes
        if ($user->isCashier()) {
            // Allow only POS routes and logout
            $allowedRoutes = ['pos.index', 'pos.checkout', 'pos.pending-orders',
                              'pos.payment.process', 'pos.order.delete',
                              'pos.receipt', 'pos.receipt.print', 'pos.receipt.kitchen', 'pos.receipt.table',
                              'logout', 'login.process'];

            $currentRoute = $request->route()?->getName();

            if (!in_array($currentRoute, $allowedRoutes)) {
                return redirect('/pos')->with('error', 'Kasir hanya dapat mengakses halaman POS.');
            }
        }

        return $next($request);
    }
}

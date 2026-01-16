<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetCompanyContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && !$user->isSuperAdmin()) {
            // Set company context from user's company
            if ($user->company_id && !Session::has('company_id')) {
                Session::put('company_id', $user->company_id);
            }

            // For Company Admin, check if they've switched to a specific branch
            if ($user->isCompanyAdmin() && Session::has('active_branch_id')) {
                Session::put('branch_id', Session::get('active_branch_id'));
            }
            // For Branch Admin, set branch context from user's branch
            elseif ($user->branch_id && !Session::has('branch_id')) {
                Session::put('branch_id', $user->branch_id);
            }
        }

        return $next($request);
    }
}

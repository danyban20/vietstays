<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets only dashboard roles (User::DASHBOARD_ROLES) into the host/admin API.
 *
 * Needed because dashboard controllers scope data only for operators
 * (host/partner) and show everything to every other role, so a customer,
 * staff or ambassador account that reached them would see every apartment,
 * booking and customer.
 */
class EnsureDashboardAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canUseDashboard()) {
            abort(403, 'This account does not have access to the host dashboard.');
        }

        return $next($request);
    }
}

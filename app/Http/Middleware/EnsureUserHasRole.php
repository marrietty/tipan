<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Allow the request through only if the authenticated user's role is one
     * of the roles permitted for this route group. The role is read from the
     * user's Role relation (role_id); any mismatch aborts with 403.
     *
     * Usage in routes: ->middleware('role:admin') or 'role:doctor,admin'.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->roleName(), $roles, true)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

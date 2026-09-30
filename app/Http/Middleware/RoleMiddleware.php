<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!in_array(auth()->user()->role->libelle, $roles, true)) {
            return redirect()->route('accueil')
                ->with('error', 'Accès non autorisé.');
        }

        return $next($request);
    }
}
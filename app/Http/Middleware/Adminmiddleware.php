<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role_id !== 8) {
            return redirect()->route('accueil')
                ->with('error', 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
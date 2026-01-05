<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        if (auth()->user()->role !== $role) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}

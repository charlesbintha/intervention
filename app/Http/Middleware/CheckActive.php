<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            $user->currentAccessToken()?->delete();

            if ($request->hasSession()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Votre compte a été désactivé.'], 403);
            }

            return redirect()->route('login')->with('error', 'Votre compte a été désactivé. Veuillez contacter l’administrateur.');
        }

        if ($request->hasSession() && Auth::guard('web')->check()) {
            $lastActivityAt = (int) $request->session()->get('auth.last_activity_at', now()->timestamp);
            $lifetimeInSeconds = max(1, (int) config('session.lifetime')) * 60;

            if ((now()->timestamp - $lastActivityAt) >= $lifetimeInSeconds) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Votre session a expiré pour cause d’inactivité. Veuillez vous reconnecter.');
            }

            $request->session()->put('auth.last_activity_at', now()->timestamp);
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        return $response;
    }
}

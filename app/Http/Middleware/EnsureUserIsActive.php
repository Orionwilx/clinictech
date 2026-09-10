<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Expulsa a los usuarios desactivados (is_active = false) hacia la pantalla
 * de cuenta inactiva, cerrando su sesión. Cubre tanto el login de un usuario
 * ya inactivo como la desactivación en caliente de una sesión abierta.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('account.inactive');
        }

        return $next($request);
    }
}

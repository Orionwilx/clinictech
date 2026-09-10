<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTechnicianProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->technician) {
            return redirect()->route('dashboard')
                ->with('error', 'Tu cuenta no tiene un perfil de técnico asociado. Contacta al administrador.');
        }

        return $next($request);
    }
}

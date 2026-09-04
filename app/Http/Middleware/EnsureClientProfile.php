<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->client) {
            return redirect()->route('dashboard')
                ->with('error', 'Tu cuenta no tiene un perfil de empresa asociado. Contacta al administrador.');
        }

        return $next($request);
    }
}

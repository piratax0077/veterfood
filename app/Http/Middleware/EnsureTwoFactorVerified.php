<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('two_factor.enabled', true)) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user || !$user->tieneRol('admin', 'auditor') || in_array(mb_strtolower($user->email), config('two_factor.bypass_emails', []), true)) {
            return $next($request);
        }

        if (!$user->two_factor_secret || !$user->two_factor_confirmed_at) {
            return redirect()->guest(route('two-factor.setup'));
        }

        if (!$request->session()->get('two_factor_verified', false)) {
            return redirect()->guest(route('two-factor.challenge'));
        }

        return $next($request);
    }
}

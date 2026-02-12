<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureVaultUnlocked
{
    /**
     * Ensure user has selected workspace, set master password, and vault is unlocked.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        // 1. Must have selected a workspace first (after login)
        if (! $request->session()->has('current_workspace_id')) {
            if ($request->routeIs('workspace.select') || $request->routeIs('workspace.choose') || $request->routeIs('workspace.create-from-select')) {
                return $next($request);
            }

            return redirect()->guest(route('workspace.select'));
        }

        if (! $user->hasMasterPassword()) {
            if ($request->routeIs('master-password.*')) {
                return $next($request);
            }

            return redirect()->guest(route('master-password.set'));
        }

        if (! $request->session()->has('vault_key')) {
            if ($request->routeIs('vault.unlock') || $request->routeIs('vault.unlock.store') || $request->routeIs('master-password.*')) {
                return $next($request);
            }

            return redirect()->guest(route('vault.unlock'));
        }

        return $next($request);
    }
}

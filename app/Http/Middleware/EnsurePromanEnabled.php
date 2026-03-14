<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePromanEnabled
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = $request->session()->get('current_workspace_id');
        if (! $workspaceId) {
            return redirect()->route('workspace.select');
        }

        $workspace = Workspace::where('user_id', $request->user()->id)->find($workspaceId);
        if (! $workspace || ! $workspace->proman_enabled) {
            abort(403, __('Fitur Proman tidak diaktifkan untuk workspace ini.'));
        }

        return $next($request);
    }
}

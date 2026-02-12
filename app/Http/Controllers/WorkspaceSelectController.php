<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceSelectController extends Controller
{
    /**
     * Show workspace selection (card grid) after login.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $workspaces = $request->user()->workspaces()->orderBy('name')->get();

        return view('auth.select-workspace', compact('workspaces'));
    }

    /**
     * Choose workspace: set session and redirect to master password (set or unlock).
     */
    public function choose(Request $request, Workspace $workspace): RedirectResponse
    {
        if ($workspace->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->session()->put('current_workspace_id', $workspace->id);

        if (! $request->user()->hasMasterPassword()) {
            return redirect()->route('master-password.set');
        }

        return redirect()->route('vault.unlock');
    }

    /**
     * Create workspace from select page (no workspace yet).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255']);

        $workspace = $request->user()->workspaces()->create($request->only('name'));

        $request->session()->put('current_workspace_id', $workspace->id);

        if (! $request->user()->hasMasterPassword()) {
            return redirect()->route('master-password.set');
        }

        return redirect()->route('vault.unlock');
    }

    /**
     * Switch workspace from sidebar (already unlocked).
     */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate(['workspace_id' => 'required|exists:workspaces,id']);

        $workspace = Workspace::findOrFail($request->workspace_id);
        if ($workspace->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->session()->put('current_workspace_id', $workspace->id);

        return redirect()->route('dashboard');
    }
}

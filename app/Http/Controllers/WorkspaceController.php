<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function index(Request $request): View
    {
        $workspaces = $request->user()->workspaces()->orderBy('name')->get();
        $currentWorkspace = null;
        $workspaceId = $request->session()->get('current_workspace_id');
        if ($workspaceId) {
            $currentWorkspace = $workspaces->firstWhere('id', $workspaceId);
        }

        return view('settings.workspace.index', compact('workspaces', 'currentWorkspace'));
    }

    public function create(): View
    {
        return view('settings.workspace.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255']);

        $request->user()->workspaces()->create($request->only('name'));

        return redirect()->route('settings.workspace.index')->with('success', __('Workspace dibuat.'));
    }

    public function edit(Workspace $workspace): View|RedirectResponse
    {
        if ($workspace->user_id !== request()->user()->id) {
            abort(403);
        }

        return view('settings.workspace.edit', compact('workspace'));
    }

    public function update(Request $request, Workspace $workspace): RedirectResponse
    {
        if ($workspace->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate(['name' => 'required|string|max:255']);
        $workspace->update($request->only('name'));

        return redirect()->route('settings.workspace.index')->with('success', __('Workspace diperbarui.'));
    }

    public function destroy(Request $request, Workspace $workspace): RedirectResponse
    {
        if ($workspace->user_id !== $request->user()->id) {
            abort(403);
        }

        $workspace->delete();

        return redirect()->route('settings.workspace.index')->with('success', __('Workspace dihapus.'));
    }
}

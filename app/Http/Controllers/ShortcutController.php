<?php

namespace App\Http\Controllers;

use App\Models\Shortcut;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShortcutController extends Controller
{
    private function getCurrentWorkspace(Request $request): ?Workspace
    {
        $workspaceId = $request->session()->get('current_workspace_id');
        if (! $workspaceId) {
            return null;
        }

        $workspace = Workspace::where('user_id', $request->user()->id)->find($workspaceId);

        return $workspace;
    }

    public function index(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $shortcuts = $workspace->shortcuts;

        return view('shortcuts.index', compact('shortcuts', 'workspace'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        return view('shortcuts.create', compact('workspace'));
    }

    public function store(Request $request): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string|url|max:2048',
            'icon' => 'nullable|string|max:100',
        ]);

        $order = $workspace->shortcuts()->max('order') + 1;
        $workspace->shortcuts()->create([
            'user_id' => $request->user()->id,
            'title' => $request->input('title'),
            'url' => $request->input('url'),
            'icon' => $request->input('icon'),
            'order' => $order,
        ]);

        return redirect()->route('shortcuts.index')->with('success', __('Pintasan ditambah.'));
    }

    public function edit(Request $request, Shortcut $shortcut): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        if ($shortcut->workspace_id !== $workspace->id || $shortcut->user_id !== $request->user()->id) {
            abort(403);
        }

        return view('shortcuts.edit', compact('shortcut', 'workspace'));
    }

    public function update(Request $request, Shortcut $shortcut): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        if ($shortcut->workspace_id !== $workspace->id || $shortcut->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string|url|max:2048',
            'icon' => 'nullable|string|max:100',
        ]);

        $shortcut->update($request->only(['title', 'url', 'icon']));

        return redirect()->route('shortcuts.index')->with('success', __('Pintasan diperbarui.'));
    }

    public function destroy(Request $request, Shortcut $shortcut): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        if ($shortcut->workspace_id !== $workspace->id || $shortcut->user_id !== $request->user()->id) {
            abort(403);
        }

        $shortcut->delete();

        return redirect()->route('shortcuts.index')->with('success', __('Pintasan dihapus.'));
    }
}

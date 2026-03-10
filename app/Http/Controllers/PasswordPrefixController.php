<?php

namespace App\Http\Controllers;

use App\Models\PasswordPrefix;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordPrefixController extends Controller
{
    private function getCurrentWorkspace(Request $request): ?Workspace
    {
        $workspaceId = $request->session()->get('current_workspace_id');
        if (! $workspaceId) {
            return null;
        }

        return Workspace::where('user_id', $request->user()->id)->find($workspaceId);
    }

    public function index(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $prefixes = $workspace->passwordPrefixes()->orderBy('name')->get();

        return view('password-prefixes.index', compact('prefixes', 'workspace'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $shortcuts = $workspace->shortcuts()->orderBy('title')->get();

        return view('password-prefixes.create', compact('workspace', 'shortcuts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'shortcut_id' => 'nullable|exists:shortcuts,id',
        ]);

        $shortcutId = $request->input('shortcut_id');
        if ($shortcutId) {
            $shortcut = $workspace->shortcuts()->find($shortcutId);
            $shortcutId = $shortcut ? $shortcut->id : null;
        }

        $workspace->passwordPrefixes()->create([
            'name' => $request->input('name'),
            'shortcut_id' => $shortcutId,
        ]);

        return redirect()->route('password-prefixes.index')->with('success', __('Prefix ditambah.'));
    }

    public function edit(Request $request, PasswordPrefix $passwordPrefix): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        if ($passwordPrefix->workspace_id !== $workspace->id) {
            abort(403);
        }

        $shortcuts = $workspace->shortcuts()->orderBy('title')->get();

        return view('password-prefixes.edit', compact('passwordPrefix', 'workspace', 'shortcuts'));
    }

    public function update(Request $request, PasswordPrefix $passwordPrefix): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        if ($passwordPrefix->workspace_id !== $workspace->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'shortcut_id' => 'nullable|exists:shortcuts,id',
        ]);

        $shortcutId = $request->input('shortcut_id');
        if ($shortcutId) {
            $shortcut = $workspace->shortcuts()->find($shortcutId);
            $shortcutId = $shortcut ? $shortcut->id : null;
        } else {
            $shortcutId = null;
        }

        $passwordPrefix->update([
            'name' => $request->input('name'),
            'shortcut_id' => $shortcutId,
        ]);

        return redirect()->route('password-prefixes.index')->with('success', __('Prefix diperbarui.'));
    }

    public function destroy(Request $request, PasswordPrefix $passwordPrefix): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        if ($passwordPrefix->workspace_id !== $workspace->id) {
            abort(403);
        }

        $passwordPrefix->delete();

        return redirect()->route('password-prefixes.index')->with('success', __('Prefix dihapus.'));
    }
}

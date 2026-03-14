<?php

namespace App\Http\Controllers;

use App\Models\PromanProject;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromanProjectController extends Controller
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

        $projects = $workspace->promanProjects()->orderBy('name')->get();

        return view('settings.workspace.proman-projects', compact('workspace', 'projects'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        return view('settings.workspace.proman-project-form', ['workspace' => $workspace, 'project' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $validated = $request->validate([
            'id_project' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace->promanProjects()->create($validated);

        return redirect()->route('settings.workspace.proman.index')->with('success', __('Project Proman ditambah.'));
    }

    public function edit(Request $request, PromanProject $promanProject): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace || $promanProject->workspace_id !== $workspace->id) {
            abort(403);
        }

        return view('settings.workspace.proman-project-form', ['workspace' => $workspace, 'project' => $promanProject]);
    }

    public function update(Request $request, PromanProject $promanProject): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace || $promanProject->workspace_id !== $workspace->id) {
            abort(403);
        }

        $validated = $request->validate([
            'id_project' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $promanProject->update($validated);

        return redirect()->route('settings.workspace.proman.index')->with('success', __('Project Proman diperbarui.'));
    }

    public function destroy(Request $request, PromanProject $promanProject): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace || $promanProject->workspace_id !== $workspace->id) {
            abort(403);
        }

        $promanProject->delete();

        return redirect()->route('settings.workspace.proman.index')->with('success', __('Project Proman dihapus.'));
    }
}

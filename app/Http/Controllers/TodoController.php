<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodoController extends Controller
{
    private function getCurrentWorkspace(Request $request): ?Workspace
    {
        $workspaceId = $request->session()->get('current_workspace_id');
        if (! $workspaceId) {
            return null;
        }

        return Workspace::where('user_id', $request->user()->id)->find($workspaceId);
    }

    private function ensureTodoInWorkspace(Request $request, Todo $todo): void
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace || $todo->workspace_id !== $workspace->id) {
            abort(403);
        }
    }

    public function index(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $query = $workspace->todos();

        if ($request->filled('date')) {
            $query->whereDate('due_date', $request->input('date'));
        }

        $todos = $query->with('shortcut')->get();
        $viewMode = in_array($request->input('view'), ['list', 'kanban'], true)
            ? $request->input('view')
            : 'list';

        return view('todos.index', compact('todos', 'viewMode'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $shortcuts = $workspace->shortcuts()->orderBy('title')->get();

        return view('todos.create', compact('shortcuts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'shortcut_id' => 'nullable|exists:shortcuts,id',
            'status' => 'nullable|in:todo,in_progress,done',
            'due_date' => 'nullable|date',
        ]);

        if (! empty($validated['shortcut_id'])) {
            $shortcut = $workspace->shortcuts()->find($validated['shortcut_id']);
            if (! $shortcut) {
                $validated['shortcut_id'] = null;
            }
        } else {
            $validated['shortcut_id'] = null;
        }

        $maxPosition = $workspace->todos()->max('position') ?? 0;

        Todo::create([
            'workspace_id' => $workspace->id,
            'shortcut_id' => $validated['shortcut_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? Todo::STATUS_TODO,
            'due_date' => $validated['due_date'] ?? null,
            'position' => $maxPosition + 1,
        ]);

        return redirect()->route('todos.index')->with('success', __('Todo ditambah.'));
    }

    public function edit(Request $request, Todo $todo): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        $this->ensureTodoInWorkspace($request, $todo);
        $shortcuts = $workspace->shortcuts()->orderBy('title')->get();

        return view('todos.edit', compact('todo', 'shortcuts'));
    }

    public function update(Request $request, Todo $todo): RedirectResponse
    {
        $this->ensureTodoInWorkspace($request, $todo);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'shortcut_id' => 'nullable|exists:shortcuts,id',
            'status' => 'required|in:todo,in_progress,done',
            'due_date' => 'nullable|date',
        ]);

        if (! empty($validated['shortcut_id'])) {
            $shortcut = $todo->workspace->shortcuts()->find($validated['shortcut_id']);
            $validated['shortcut_id'] = $shortcut ? $shortcut->id : null;
        } else {
            $validated['shortcut_id'] = null;
        }

        $todo->update($validated);

        return redirect()->route('todos.index')->with('success', __('Todo diperbarui.'));
    }

    public function destroy(Request $request, Todo $todo): RedirectResponse
    {
        $this->ensureTodoInWorkspace($request, $todo);
        $todo->delete();
        return redirect()->route('todos.index')->with('success', __('Todo dihapus.'));
    }

    public function updateStatus(Request $request, Todo $todo): RedirectResponse|JsonResponse
    {
        $this->ensureTodoInWorkspace($request, $todo);

        $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $todo->update(['status' => $request->input('status')]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        $view = $request->input('view', 'list');
        $date = $request->input('date');
        $redirect = route('todos.index');
        $params = [];
        if (in_array($view, ['list', 'kanban'], true)) {
            $params['view'] = $view;
        }
        if ($date) {
            $params['date'] = $date;
        }

        return redirect()->to($params ? $redirect . '?' . http_build_query($params) : $redirect)
            ->with('success', __('Status diperbarui.'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportTodosRequest;
use App\Jobs\SubmitPromanTaskJob;
use App\Jobs\UpdatePromanProgressJob;
use App\Models\PromanTask;
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

        $todos = $query->with(['shortcut', 'promanProject'])->get();
        $viewMode = in_array($request->input('view'), ['list', 'kanban', 'proman'], true)
            ? $request->input('view')
            : 'list';
        $promanProjects = $workspace->proman_enabled ? $workspace->promanProjects()->orderBy('name')->get() : collect();
        $promanTasks = $workspace->proman_enabled
            ? PromanTask::whereHas('todo', fn ($q) => $q->where('workspace_id', $workspace->id))
                ->with('todo')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('todos.index', compact('todos', 'viewMode', 'workspace', 'promanProjects', 'promanTasks'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $shortcuts = $workspace->shortcuts()->orderBy('title')->get();
        $promanProjects = $workspace->proman_enabled ? $workspace->promanProjects()->orderBy('name')->get() : collect();

        return view('todos.create', compact('shortcuts', 'promanProjects', 'workspace'));
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
            'proman_project_id' => 'nullable|exists:proman_projects,id',
            'proman_submit_scheduled_at' => 'nullable|date',
            'proman_submit_now' => 'nullable|boolean',
        ]);

        if (! empty($validated['shortcut_id'])) {
            $shortcut = $workspace->shortcuts()->find($validated['shortcut_id']);
            if (! $shortcut) {
                $validated['shortcut_id'] = null;
            }
        } else {
            $validated['shortcut_id'] = null;
        }

        $promanProjectId = null;
        $promanSubmitScheduledAt = null;
        if ($workspace->proman_enabled) {
            if (! empty($validated['proman_project_id'])) {
                $project = $workspace->promanProjects()->find($validated['proman_project_id']);
                $promanProjectId = $project?->id;
            }
            if (! empty($validated['proman_submit_scheduled_at'])) {
                $promanSubmitScheduledAt = $validated['proman_submit_scheduled_at'];
            }
            if (! empty($validated['proman_submit_now'])) {
                $promanSubmitScheduledAt = now();
            }
        }

        $maxPosition = $workspace->todos()->max('position') ?? 0;

        $todo = Todo::create([
            'workspace_id' => $workspace->id,
            'shortcut_id' => $validated['shortcut_id'] ?? null,
            'proman_project_id' => $promanProjectId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? Todo::STATUS_TODO,
            'due_date' => $validated['due_date'] ?? null,
            'position' => $maxPosition + 1,
            'proman_submit_scheduled_at' => $promanSubmitScheduledAt,
        ]);

        if ($promanSubmitScheduledAt && $todo->status === Todo::STATUS_DONE) {
            SubmitPromanTaskJob::dispatch($todo->id);
        }

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
        $promanProjects = $workspace->proman_enabled ? $workspace->promanProjects()->orderBy('name')->get() : collect();

        return view('todos.edit', compact('todo', 'shortcuts', 'promanProjects', 'workspace'));
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
            'proman_project_id' => 'nullable|exists:proman_projects,id',
            'proman_submit_scheduled_at' => 'nullable|date',
            'proman_submit_now' => 'nullable|boolean',
        ]);

        if (! empty($validated['shortcut_id'])) {
            $shortcut = $todo->workspace->shortcuts()->find($validated['shortcut_id']);
            $validated['shortcut_id'] = $shortcut ? $shortcut->id : null;
        } else {
            $validated['shortcut_id'] = null;
        }

        if ($todo->workspace->proman_enabled) {
            if (isset($validated['proman_project_id']) && $validated['proman_project_id']) {
                $project = $todo->workspace->promanProjects()->find($validated['proman_project_id']);
                $validated['proman_project_id'] = $project?->id;
            } else {
                $validated['proman_project_id'] = null;
            }
            if (! empty($validated['proman_submit_now'])) {
                $validated['proman_submit_scheduled_at'] = now();
            } elseif (! empty($validated['proman_submit_scheduled_at'])) {
                $validated['proman_submit_scheduled_at'] = $validated['proman_submit_scheduled_at'];
            } else {
                $validated['proman_submit_scheduled_at'] = $todo->proman_submit_scheduled_at;
            }
        } else {
            unset($validated['proman_project_id'], $validated['proman_submit_scheduled_at']);
        }
        unset($validated['proman_submit_now']);

        $todo->update($validated);

        if (! empty($validated['proman_submit_scheduled_at'] ?? null) && $todo->status === Todo::STATUS_DONE && ! $todo->proman_submitted_at) {
            SubmitPromanTaskJob::dispatch($todo->id);
        }

        if ($todo->status === Todo::STATUS_DONE) {
            $todo->load('promanTask');
            if ($todo->promanTask && ! $todo->promanTask->isCompleted()) {
                UpdatePromanProgressJob::dispatch($todo->id);
            }
        }

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

        $newStatus = $request->input('status');
        $todo->update(['status' => $newStatus]);

        if ($newStatus === Todo::STATUS_DONE) {
            $todo->load('promanTask');
            if ($todo->promanTask && ! $todo->promanTask->isCompleted()) {
                UpdatePromanProgressJob::dispatch($todo->id);
            }
        }

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

        return redirect()->to($params ? $redirect.'?'.http_build_query($params) : $redirect)
            ->with('success', __('Status diperbarui.'));
    }

    public function importFromJson(ImportTodosRequest $request): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        if (! $workspace->proman_enabled) {
            abort(403, __('Fitur Proman tidak diaktifkan untuk workspace ini.'));
        }

        $decoded = json_decode($request->input('json'), true);
        if (! is_array($decoded)) {
            return redirect()->back()->withErrors(['json' => __('JSON tidak valid.')]);
        }

        $items = isset($decoded['todos']) && is_array($decoded['todos']) ? $decoded['todos'] : $decoded;
        $created = 0;
        $maxPosition = $workspace->todos()->max('position') ?? 0;
        $projectMap = $workspace->promanProjects()->get()->keyBy('id_project');

        foreach ($items as $item) {
            if (empty($item['title'] ?? null) || ! is_string($item['title'])) {
                continue;
            }
            $promanProjectId = null;
            if (! empty($item['id_project']) && $projectMap->has($item['id_project'])) {
                $promanProjectId = $projectMap->get($item['id_project'])->id;
            }
            $dueDate = null;
            if (! empty($item['due_date'])) {
                $parsed = \Carbon\Carbon::parse($item['due_date']);
                $dueDate = $parsed->isValid() ? $parsed->format('Y-m-d') : null;
            }
            $maxPosition++;
            Todo::create([
                'workspace_id' => $workspace->id,
                'title' => $item['title'],
                'description' => $item['description'] ?? null,
                'status' => Todo::STATUS_TODO,
                'due_date' => $dueDate,
                'position' => $maxPosition,
                'proman_project_id' => $promanProjectId,
            ]);
            $created++;
        }

        return redirect()->route('todos.index')->with('success', __(':count todo diimpor.', ['count' => $created]));
    }

    public function batchAssignProject(Request $request): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace || ! $workspace->proman_enabled) {
            abort(403);
        }

        $request->validate([
            'todo_ids' => ['required', 'array'],
            'todo_ids.*' => ['integer', 'exists:todos,id'],
            'proman_project_id' => ['nullable', 'exists:proman_projects,id'],
        ]);

        $todoIds = $request->input('todo_ids', []);
        $projectId = $request->input('proman_project_id');
        $todos = $workspace->todos()->whereIn('id', $todoIds)->get();
        foreach ($todos as $todo) {
            $todo->update(['proman_project_id' => $projectId ?: null]);
        }

        return redirect()->route('todos.index')->with('success', __('Project Proman diperbarui untuk :count todo.', ['count' => $todos->count()]));
    }

    public function batchScheduleSubmit(Request $request): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace || ! $workspace->proman_enabled) {
            abort(403);
        }

        $request->validate([
            'todo_ids' => ['required', 'array'],
            'todo_ids.*' => ['integer', 'exists:todos,id'],
            'proman_submit_scheduled_at' => ['nullable', 'date'],
            'submit_now' => ['nullable', 'boolean'],
        ]);

        $todoIds = $request->input('todo_ids', []);
        $scheduledAt = $request->boolean('submit_now') ? now() : $request->input('proman_submit_scheduled_at');
        $todos = $workspace->todos()->whereIn('id', $todoIds)->whereNotNull('proman_project_id')->get();
        foreach ($todos as $todo) {
            $todo->update(['proman_submit_scheduled_at' => $scheduledAt]);
            if ($scheduledAt && $todo->status === Todo::STATUS_DONE && ! $todo->proman_submitted_at) {
                SubmitPromanTaskJob::dispatch($todo->id);
            }
        }

        return redirect()->route('todos.index')->with('success', __('Jadwal submit Proman diperbarui untuk :count todo.', ['count' => $todos->count()]));
    }
}

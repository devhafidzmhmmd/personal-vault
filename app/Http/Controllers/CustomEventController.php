<?php

namespace App\Http\Controllers;

use App\Models\CustomEvent;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomEventController extends Controller
{
    private function getCurrentWorkspace(Request $request): ?Workspace
    {
        $workspaceId = $request->session()->get('current_workspace_id');
        if (! $workspaceId) {
            return null;
        }

        return Workspace::where('user_id', $request->user()->id)->find($workspaceId);
    }

    private function ensureEventInWorkspace(Request $request, CustomEvent $customEvent): void
    {
        $customEvent->load('workspace');
        if ($customEvent->workspace->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $date = $request->filled('date') ? $request->input('date') : null;

        return view('custom-events.create', compact('date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_date',
            'description' => 'nullable|string|max:5000',
            'is_special' => 'nullable|boolean',
        ]);

        $endDate = $validated['event_end_date'] ?? $validated['event_date'];

        CustomEvent::create([
            'workspace_id' => $workspace->id,
            'title' => $validated['title'],
            'event_date' => $validated['event_date'],
            'event_end_date' => $endDate,
            'description' => $validated['description'] ?? null,
            'is_special' => $request->boolean('is_special'),
        ]);

        $redirect = route('dashboard');
        $params = $request->only(['year', 'month']);
        if (! empty($params)) {
            $redirect .= '?'.http_build_query($params);
        }

        return redirect()->to($redirect)->with('success', __('Event ditambah.'));
    }

    public function edit(Request $request, CustomEvent $customEvent): View|RedirectResponse
    {
        $this->ensureEventInWorkspace($request, $customEvent);

        return view('custom-events.edit', compact('customEvent'));
    }

    public function update(Request $request, CustomEvent $customEvent): RedirectResponse
    {
        $this->ensureEventInWorkspace($request, $customEvent);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_date',
            'description' => 'nullable|string|max:5000',
            'is_special' => 'nullable|boolean',
        ]);

        $validated['event_end_date'] = $validated['event_end_date'] ?? $validated['event_date'];
        $validated['is_special'] = $request->boolean('is_special');
        $customEvent->update($validated);

        $redirect = route('dashboard');
        $params = $request->only(['year', 'month']);
        if (! empty($params)) {
            $redirect .= '?'.http_build_query($params);
        }

        return redirect()->to($redirect)->with('success', __('Event diperbarui.'));
    }

    public function destroy(Request $request, CustomEvent $customEvent): RedirectResponse
    {
        $this->ensureEventInWorkspace($request, $customEvent);
        $customEvent->delete();

        $redirect = route('dashboard');
        $params = $request->only(['year', 'month']);
        if (! empty($params)) {
            $redirect .= '?'.http_build_query($params);
        }

        return redirect()->to($redirect)->with('success', __('Event dihapus.'));
    }
}

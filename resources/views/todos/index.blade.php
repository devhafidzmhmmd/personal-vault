@extends('layouts.admin')

@section('title', __('Todo'))

@push('styles')
<style>
.kanban-column-cards:has(.kanban-card) .empty-placeholder { display: none; }
</style>
@endpush

@section('content')
    @php
        $currentDate = request('date');
        $baseUrl = route('todos.index');
        $queryParams = array_filter(['date' => $currentDate]);
        $urlList = $currentDate ? $baseUrl . '?' . http_build_query(array_merge($queryParams, ['view' => 'list'])) : $baseUrl . '?view=list';
        $urlKanban = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['view' => 'kanban']));
        $urlProman = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['view' => 'proman']));
    @endphp
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ __('Todo') }}</h1>
        <div class="flex flex-wrap gap-2">
            @if(isset($workspace) && $workspace->proman_enabled)
            <button type="button" data-modal-target="import-json-modal" data-modal-toggle="import-json-modal" class="text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 font-medium rounded-lg text-sm px-4 py-2">
                {{ __('Import JSON') }}
            </button>
            @endif
            <a href="{{ route('todos.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                {{ __('Tambah Todo') }}
            </a>
        </div>
    </div>

    @if(isset($workspace) && $workspace->proman_enabled)
    <div id="import-json-modal" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center w-full p-4 overflow-x-hidden overflow-y-auto bg-gray-500/50 dark:bg-gray-900/80">
        <div class="relative w-full max-w-2xl rounded-lg bg-white dark:bg-gray-800 shadow">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('Import Todo dari JSON') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('Format: array of objects dengan "title", atau object dengan key "todos". Opsional: description, due_date, id_project.') }}</p>
                <form action="{{ route('todos.import-json') }}" method="POST">
                    @csrf
                    <textarea name="json" id="import-json-field" rows="12" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 block w-full p-2.5 font-mono" placeholder='[{"title": "Task 1", "description": "...", "due_date": "2026-01-25", "id_project": "uuid"}]'>{{ old('json') }}</textarea>
                    @error('json')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <div class="mt-4 flex gap-2">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2">{{ __('Import') }}</button>
                        <button type="button" data-modal-hide="import-json-modal" class="text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 font-medium rounded-lg text-sm px-4 py-2">{{ __('Batal') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-green-900/20 dark:text-green-400">{{ session('success') }}</div>
    @endif

    <div class="mb-4 flex gap-2 border-b border-gray-200 dark:border-gray-700">
        <a href="{{ $urlList }}" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $viewMode === 'list' ? 'bg-white dark:bg-gray-800 border border-b-0 border-gray-200 dark:border-gray-700 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
            {{ __('Daftar') }}
        </a>
        <a href="{{ $urlKanban }}" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $viewMode === 'kanban' ? 'bg-white dark:bg-gray-800 border border-b-0 border-gray-200 dark:border-gray-700 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
            {{ __('Kanban') }}
        </a>
        @if(isset($workspace) && $workspace->proman_enabled)
        <a href="{{ $urlProman }}" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $viewMode === 'proman' ? 'bg-white dark:bg-gray-800 border border-b-0 border-gray-200 dark:border-gray-700 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
            {{ __('Riwayat Proman') }}
        </a>
        @endif
    </div>

    @if($viewMode === 'list')
        @if(isset($workspace) && $workspace->proman_enabled && $promanProjects->isNotEmpty() && $todos->isNotEmpty())
        <div class="mb-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-wrap items-end gap-4">
            <form id="batch-assign-form" method="POST" action="{{ route('todos.batch-assign-project') }}" class="flex flex-wrap items-end gap-4">
                @csrf
                <div id="batch-assign-ids"></div>
                <div>
                    <label for="batch_proman_project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Assign project') }}</label>
                    <select name="proman_project_id" id="batch_proman_project_id" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm p-2">
                        <option value="">{{ __('— Tidak ubah —') }}</option>
                        @foreach($promanProjects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" id="batch-assign-btn" class="text-white bg-blue-700 hover:bg-blue-800 text-sm font-medium rounded-lg px-4 py-2">{{ __('Assign project') }}</button>
            </form>
            <form id="batch-schedule-form" method="POST" action="{{ route('todos.batch-schedule-submit') }}" class="flex flex-wrap items-end gap-4">
                @csrf
                <div id="batch-schedule-ids"></div>
                <div>
                    <label for="batch_proman_submit_scheduled_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Jadwal submit') }}</label>
                    <input type="datetime-local" name="proman_submit_scheduled_at" id="batch_proman_submit_scheduled_at" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm p-2">
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="submit_now" value="0">
                    <input type="checkbox" name="submit_now" id="batch_submit_now" value="1" class="w-4 h-4 rounded">
                    <label for="batch_submit_now" class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Langsung kirim') }}</label>
                </div>
                <button type="submit" id="batch-schedule-btn" class="text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 text-sm font-medium rounded-lg px-4 py-2">{{ __('Jadwalkan submit') }}</button>
            </form>
        </div>
        <script>
        document.querySelectorAll('#batch-assign-form, #batch-schedule-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var ids = Array.from(document.querySelectorAll('.todo-batch-checkbox:checked')).map(function(cb) { return cb.value; });
                if (ids.length === 0) { e.preventDefault(); alert('{{ __('Pilih minimal satu todo.') }}'); return; }
                var container = form.querySelector('div[id$="-ids"]');
                container.innerHTML = '';
                ids.forEach(function(id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'todo_ids[]';
                    input.value = id;
                    container.appendChild(input);
                });
            });
        });
        </script>
        @endif
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @if(isset($workspace) && $workspace->proman_enabled)
                        <th class="px-6 py-3 w-10"><span class="sr-only">{{ __('Pilih') }}</span></th>
                        @endif
                        <th class="px-6 py-3">{{ __('Judul') }}</th>
                        <th class="px-6 py-3">{{ __('Deskripsi') }}</th>
                        <th class="px-6 py-3">{{ __('Pintasan') }}</th>
                        @if(isset($workspace) && $workspace->proman_enabled)
                        <th class="px-6 py-3">{{ __('Project Proman') }}</th>
                        @endif
                        <th class="px-6 py-3">{{ __('Status') }}</th>
                        <th class="px-6 py-3">{{ __('Jatuh tempo') }}</th>
                        <th class="px-6 py-3">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todos as $todo)
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                            @if(isset($workspace) && $workspace->proman_enabled)
                            <td class="px-6 py-4">
                                <input type="checkbox" name="todo_ids[]" value="{{ $todo->id }}" class="todo-batch-checkbox w-4 h-4 rounded">
                            </td>
                            @endif
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $todo->title }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $todo->description }}">{{ Str::limit($todo->description, 40) ?: '—' }}</td>
                            <td class="px-6 py-4">
                                @if($todo->shortcut)
                                    <a href="{{ $todo->shortcut->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:underline truncate max-w-[8rem] block" title="{{ $todo->shortcut->url }}">{{ $todo->shortcut->title }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            @if(isset($workspace) && $workspace->proman_enabled)
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $todo->promanProject?->name ?? '—' }}</td>
                            @endif
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded {{ $todo->status === 'done' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($todo->status === 'in_progress' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200') }}">
                                    {{ \App\Models\Todo::statuses()[$todo->status] ?? $todo->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $todo->due_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('todos.edit', $todo) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Edit') }}</a>
                                <form action="{{ route('todos.destroy', $todo) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('{{ __('Hapus todo ini?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">{{ __('Hapus') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ isset($workspace) && $workspace->proman_enabled ? 8 : 6 }}" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">{{ $currentDate ? __('Tidak ada todo untuk tanggal ini.') : __('Belum ada todo.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($viewMode === 'proman')
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3">{{ __('Todo') }}</th>
                        <th class="px-6 py-3">{{ __('ID Task') }}</th>
                        <th class="px-6 py-3">{{ __('ID Project') }}</th>
                        <th class="px-6 py-3">{{ __('Status di Proman') }}</th>
                        <th class="px-6 py-3">{{ __('Dibuat') }}</th>
                        <th class="px-6 py-3">{{ __('Selesai di Proman') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promanTasks as $pt)
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                @if($pt->todo)
                                    <a href="{{ route('todos.edit', $pt->todo) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $pt->todo->title }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-600 dark:text-gray-400 text-xs">{{ $pt->id_task }}</td>
                            <td class="px-6 py-4 font-mono text-gray-600 dark:text-gray-400 text-xs">{{ $pt->id_project }}</td>
                            <td class="px-6 py-4">
                                @if($pt->isCompleted())
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">{{ __('Selesai') }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">{{ __('Belum selesai') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $pt->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $pt->progress_completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('Belum ada task yang dikirim ke Proman.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="kanban-board">
            @foreach(['todo' => __('Todo'), 'in_progress' => __('Sedang dikerjakan'), 'done' => __('Selesai')] as $statusKey => $statusLabel)
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700 min-h-[12rem]">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ $statusLabel }}</h3>
                    <div class="kanban-column-cards space-y-2 min-h-[4rem]" data-status="{{ $statusKey }}">
                        @forelse($todos->where('status', $statusKey) as $todo)
                            <div class="kanban-card bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3 shadow-sm flex items-start gap-2"
                                data-todo-id="{{ $todo->id }}"
                                data-update-url="{{ route('todos.update-status', $todo) }}">
                                <span class="kanban-drag-handle cursor-grab active:cursor-grabbing shrink-0 mt-0.5 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300" aria-hidden="true">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H8a1 1 0 01-1-1V2zM7 6a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H8a1 1 0 01-1-1V6zM7 10a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H8a1 1 0 01-1-1v-3zM13 2a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V2zM13 6a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V6zM13 10a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-3z"></path></svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $todo->title }}</p>
                                    @if($todo->shortcut)
                                        <a href="{{ $todo->shortcut->url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-600 dark:text-blue-400 hover:underline truncate block mt-0.5">{{ $todo->shortcut->title }}</a>
                                    @endif
                                    @if($todo->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2" title="{{ $todo->description }}">{{ Str::limit($todo->description, 60) }}</p>
                                    @endif
                                    @if($todo->due_date)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $todo->due_date->format('d/m/Y') }}</p>
                                    @endif
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <a href="{{ route('todos.edit', $todo) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">{{ __('Edit') }}</a>
                                        <form action="{{ route('todos.update-status', $todo) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="view" value="kanban">
                                            @if($currentDate)<input type="hidden" name="date" value="{{ $currentDate }}">@endif
                                            @if($statusKey !== 'todo')
                                                <button type="submit" name="status" value="todo" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">{{ __('→ Todo') }}</button>
                                            @endif
                                            @if($statusKey !== 'in_progress')
                                                <button type="submit" name="status" value="in_progress" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">{{ __('→ Sedang dikerjakan') }}</button>
                                            @endif
                                            @if($statusKey !== 'done')
                                                <button type="submit" name="status" value="done" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">{{ __('→ Selesai') }}</button>
                                            @endif
                                        </form>
                                        <form action="{{ route('todos.destroy', $todo) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Hapus todo ini?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline">{{ __('Hapus') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 py-2 empty-placeholder">{{ __('Kosong') }}</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

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
    @endphp
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ __('Todo') }}</h1>
        <a href="{{ route('todos.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
            {{ __('Tambah Todo') }}
        </a>
    </div>

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
    </div>

    @if($viewMode === 'list')
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3">{{ __('Judul') }}</th>
                        <th class="px-6 py-3">{{ __('Deskripsi') }}</th>
                        <th class="px-6 py-3">{{ __('Pintasan') }}</th>
                        <th class="px-6 py-3">{{ __('Status') }}</th>
                        <th class="px-6 py-3">{{ __('Jatuh tempo') }}</th>
                        <th class="px-6 py-3">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todos as $todo)
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $todo->title }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $todo->description }}">{{ Str::limit($todo->description, 40) ?: '—' }}</td>
                            <td class="px-6 py-4">
                                @if($todo->shortcut)
                                    <a href="{{ $todo->shortcut->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:underline truncate max-w-[8rem] block" title="{{ $todo->shortcut->url }}">{{ $todo->shortcut->title }}</a>
                                @else
                                    —
                                @endif
                            </td>
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
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">{{ $currentDate ? __('Tidak ada todo untuk tanggal ini.') : __('Belum ada todo.') }}</td>
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

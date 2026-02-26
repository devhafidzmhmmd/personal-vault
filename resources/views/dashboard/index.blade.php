@extends('layouts.admin')

@section('title', __('Dashboard'))

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ __('Dashboard') }}</h1>
        <p class="text-gray-600 dark:text-gray-400">{{ __('Pintasan Anda — klik untuk membuka di tab baru') }}</p>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-green-900/20 dark:text-green-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @forelse($shortcuts as $shortcut)
                    <a href="{{ $shortcut->url }}" target="_blank" rel="noopener noreferrer"
                        class="block p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        @if($shortcut->icon)
                            <span class="text-3xl mb-2 block">{{ $shortcut->icon }}</span>
                        @else
                            <svg class="w-10 h-10 text-gray-400 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                            </svg>
                        @endif
                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ $shortcut->title }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1">{{ $shortcut->url }}</p>
                    </a>
                @empty
                    <div class="col-span-full p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-center text-gray-500 dark:text-gray-400">
                        <p>{{ __('Belum ada pintasan.') }}</p>
                        <a href="{{ route('shortcuts.create') }}" class="inline-flex mt-2 text-blue-600 dark:text-blue-400 hover:underline">{{ __('Tambah pintasan') }}</a>
                    </div>
                @endforelse
            </div>
        </div>
        <div x-data="dashboardCalendar({{ json_encode($eventsByDate ?? []) }}, {{ json_encode($dashboardParams ?? []) }})">
            @if(!empty($calendarDays))
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <a href="{{ $prevMonthUrl }}" class="p-1.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ __('Bulan sebelumnya') }}" aria-label="{{ __('Bulan sebelumnya') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        </a>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $calendarTitle }}</h2>
                        <a href="{{ $nextMonthUrl }}" class="p-1.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ __('Bulan berikutnya') }}" aria-label="{{ __('Bulan berikutnya') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        </a>
                    </div>
                    <a href="{{ route('dashboard') }}" class="block text-center text-sm text-blue-600 dark:text-blue-400 hover:underline mb-2">{{ __('Hari ini') }}</a>
                    <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-600 dark:text-gray-400 font-medium mb-2">
                        <span>{{ __('Min') }}</span><span>{{ __('Sen') }}</span><span>{{ __('Sel') }}</span><span>{{ __('Rab') }}</span><span>{{ __('Kam') }}</span><span>{{ __('Jum') }}</span><span>{{ __('Sab') }}</span>
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        @foreach($calendarDays as $cell)
                            @if($cell === null)
                                <div class="aspect-square rounded"></div>
                            @else
                                @php
                                    $classes = 'aspect-square rounded flex flex-col items-center justify-center text-sm cursor-pointer ';
                                    if ($cell['is_today']) {
                                        $classes .= 'bg-blue-600 text-white dark:bg-blue-500 ';
                                    } elseif (!empty($cell['is_holiday'])) {
                                        $classes .= 'bg-red-500 text-white dark:bg-red-600 ';
                                    } elseif (!empty($cell['is_weekend'])) {
                                        $classes .= 'bg-gray-200 dark:bg-gray-600/60 text-gray-600 dark:text-gray-400 ';
                                    } elseif ($cell['has_todo'] || !empty($cell['has_custom_event'])) {
                                        $classes .= 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 font-medium ';
                                    } else {
                                        $classes .= 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 ';
                                    }
                                @endphp
                                <button type="button" class="{{ trim($classes) }}" data-date="{{ $cell['date'] }}" @click="openDay = $event.currentTarget.dataset.date" @if(!empty($cell['holiday_name'])) title="{{ $cell['holiday_name'] }}" @endif>
                                    <span>{{ $cell['day'] }}</span>
                                    @if(!empty($cell['is_holiday']) && !$cell['is_today'])
                                        <span class="w-1 h-1 rounded-full bg-white/80 mt-0.5" aria-hidden="true"></span>
                                    @elseif($cell['has_todo'] && !$cell['is_today'] && empty($cell['is_holiday']))
                                        <span class="w-1 h-1 rounded-full bg-blue-500 dark:bg-blue-400 mt-0.5" aria-hidden="true"></span>
                                    @endif
                                    @if(!empty($cell['has_custom_event']) && !$cell['is_today'] && empty($cell['is_holiday']))
                                        <span class="text-[10px] mt-0.5" title="{{ __('Event') }}">★</span>
                                    @endif
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Modal event hari ini --}}
            <div x-show="openDay" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.self="openDay = null">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-4 max-h-[80vh] overflow-y-auto" @click.stop>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100" x-text="openDay ? formatDate(openDay) : ''"></h3>
                        <button type="button" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700" @click="openDay = null" aria-label="{{ __('Tutup') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                    <template x-if="openDay && eventsByDate[openDay]">
                        <div>
                            <ul class="space-y-2 mb-4" x-data>
                                <template x-for="ev in eventsByDate[openDay]" :key="ev.type + (ev.title || '') + (ev.id || '')">
                                    <li class="flex items-start gap-2 text-sm">
                                        <span class="shrink-0 w-16 text-xs font-medium text-gray-500 dark:text-gray-400" x-text="ev.type === 'holiday' ? '{{ __('Libur') }}' : (ev.type === 'todo' ? '{{ __('Todo') }}' : '{{ __('Event') }}')"></span>
                                        <span class="flex-1 min-w-0">
                                            <template x-if="ev.url">
                                                <a :href="ev.url" class="text-blue-600 dark:text-blue-400 hover:underline truncate block" x-text="ev.title"></a>
                                            </template>
                                            <template x-if="!ev.url">
                                                <span class="text-gray-800 dark:text-gray-200" x-text="ev.title"></span>
                                            </template>
                                            <span x-show="ev.status" class="text-xs text-gray-500 dark:text-gray-400 ml-1" x-text="ev.status ? statusLabel(ev.status) : ''"></span>
                                        </span>
                                    </li>
                                </template>
                            </ul>
                            <a :href="addEventUrl(openDay)" class="inline-flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                                {{ __('Tambah event') }}
                            </a>
                        </div>
                    </template>
                    <template x-if="openDay && (!eventsByDate[openDay] || eventsByDate[openDay].length === 0)">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('Tidak ada event untuk hari ini.') }}</p>
                            <a :href="addEventUrl(openDay)" class="inline-flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                                {{ __('Tambah event') }}
                            </a>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Agenda bulan ini --}}
            @if(!empty($eventsByDate))
                @php
                    $agendaDates = collect($eventsByDate)->filter(fn ($events) => count($events) > 0)->keys()->sort()->values()->all();
                @endphp
                @if(count($agendaDates) > 0)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">{{ __('Agenda') }} {{ $calendarTitle }}</h2>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            @foreach($agendaDates as $dateStr)
                                @php
                                    $dateObj = \Carbon\Carbon::parse($dateStr);
                                    $dayEvents = $eventsByDate[$dateStr] ?? [];
                                @endphp
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ $dateObj->translatedFormat('l, d M Y') }}</p>
                                    <ul class="space-y-1">
                                        @foreach($dayEvents as $ev)
                                            <li class="text-sm flex items-center gap-2">
                                                @if($ev['type'] === 'holiday')
                                                    <span class="shrink-0 px-1.5 py-0.5 text-xs rounded bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">{{ __('Libur') }}</span>
                                                    <span class="text-gray-800 dark:text-gray-200">{{ $ev['title'] }}</span>
                                                @elseif($ev['type'] === 'todo')
                                                    <span class="shrink-0 px-1.5 py-0.5 text-xs rounded bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">{{ __('Todo') }}</span>
                                                    <a href="{{ $ev['url'] }}" class="text-blue-600 dark:text-blue-400 hover:underline truncate min-w-0 flex-1">{{ $ev['title'] }}</a>
                                                    @if(!empty($ev['shortcut_title']))
                                                        @if(!empty($ev['shortcut_url']))
                                                            <a href="{{ $ev['shortcut_url'] }}" target="_blank" rel="noopener noreferrer" class="shrink-0 px-2 py-0.5 text-xs font-medium rounded bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 truncate max-w-[8rem]" title="{{ $ev['shortcut_title'] }}">{{ $ev['shortcut_title'] }}</a>
                                                        @else
                                                            <span class="shrink-0 px-2 py-0.5 text-xs font-medium rounded bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 truncate max-w-[8rem]" title="{{ $ev['shortcut_title'] }}">{{ $ev['shortcut_title'] }}</span>
                                                        @endif
                                                    @endif
                                                @else
                                                    <span class="shrink-0 px-1.5 py-0.5 text-xs rounded bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300">{{ __('Event') }}</span>
                                                    <a href="{{ $ev['url'] }}" class="text-blue-600 dark:text-blue-400 hover:underline truncate">{{ $ev['title'] }}</a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">{{ __('Todo hari ini') }}</h2>
                @if(isset($todosToday) && $todosToday->isNotEmpty())
                    <ul class="space-y-2">
                        @foreach($todosToday as $todo)
                            <li>
                                <a href="{{ route('todos.edit', $todo) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline block truncate">{{ $todo->title }}</a>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ \App\Models\Todo::statuses()[$todo->status] ?? $todo->status }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('todos.index') }}" class="inline-block mt-3 text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('Lihat semua Todo') }}</a>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tidak ada todo untuk hari ini.') }}</p>
                    <a href="{{ route('todos.index') }}" class="inline-block mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('Ke Todo') }}</a>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardCalendar', (eventsByDate = {}, dashboardParams = {}) => ({
                openDay: null,
                eventsByDate,
                dashboardParams,
                formatDate(ymd) {
                    if (!ymd) return '';
                    const [y, m, d] = ymd.split('-');
                    const date = new Date(y, parseInt(m, 10) - 1, parseInt(d, 10));
                    return date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                },
                addEventUrl(date) {
                    const q = new URLSearchParams({ date, ...this.dashboardParams });
                    return '{{ url("/custom-events/create") }}?' + q.toString();
                },
                statusLabel(s) {
                    const labels = { todo: '{{ __("Todo") }}', in_progress: '{{ __("Sedang dikerjakan") }}', done: '{{ __("Selesai") }}' };
                    return labels[s] || s;
                }
            }));
        });
    </script>
@endsection

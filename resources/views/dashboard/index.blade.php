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
                        @if($shortcut->favicon_url)
                            <img src="{{ $shortcut->favicon_url }}" alt="" class="w-10 h-10 mb-2 object-contain" loading="lazy">
                        @elseif($shortcut->icon)
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
        <div x-data="dashboardCalendar({{ json_encode($eventsByDate ?? []) }}, {{ json_encode($dashboardParams ?? []) }}, {{ json_encode($absenSettingsConfigured ?? false) }}, {{ json_encode($savedLocations ?? []) }}, '{{ $todayDate ?? '' }}', {{ json_encode($absenByDate ?? []) }})">
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
                                <button type="button" class="{{ trim($classes) }} relative" data-date="{{ $cell['date'] }}" @click="openDay = $event.currentTarget.dataset.date" @if(!empty($cell['holiday_name'])) title="{{ $cell['holiday_name'] }}" @endif>
                                    <span>{{ $cell['day'] }}</span>
                                    @if(!empty($cell['is_holiday']) && !$cell['is_today'])
                                        <span class="w-1 h-1 rounded-full bg-white/80 mt-0.5" aria-hidden="true"></span>
                                    @elseif($cell['has_todo'] && !$cell['is_today'] && empty($cell['is_holiday']))
                                        <span class="w-1 h-1 rounded-full bg-blue-500 dark:bg-blue-400 mt-0.5" aria-hidden="true"></span>
                                    @endif
                                    @if(!empty($cell['has_custom_event']) && !$cell['is_today'] && empty($cell['is_holiday']))
                                        <span class="text-[10px] mt-0.5" title="{{ __('Event') }}">★</span>
                                    @endif
                                    @if(!empty($cell['absen_count']))
                                        <span class="absolute top-0.5 right-0.5 min-w-[1rem] h-4 px-1 flex items-center justify-center rounded-full text-[10px] font-medium
                                            {{ $cell['absen_count'] >= 2 ? 'bg-green-500 text-white dark:bg-green-600' : 'bg-yellow-400 text-yellow-900 dark:bg-yellow-500 dark:text-yellow-950' }}"
                                            title="{{ $cell['absen_count'] }}/2 {{ __('absen') }}">
                                            {{ $cell['absen_count'] }}
                                        </span>
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
                            <div class="flex flex-wrap gap-2">
                                <a :href="addEventUrl(openDay)" class="inline-flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                                    {{ __('Tambah event') }}
                                </a>
                                <button type="button" @click="openAbsenModal(openDay)" class="inline-flex items-center gap-1 text-sm text-green-600 dark:text-green-400 hover:underline">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    {{ __('Present') }}
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="openDay && (!eventsByDate[openDay] || eventsByDate[openDay].length === 0)">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('Tidak ada event untuk hari ini.') }}</p>
                            <div class="flex flex-wrap gap-2">
                                <a :href="addEventUrl(openDay)" class="inline-flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                                    {{ __('Tambah event') }}
                                </a>
                                <button type="button" @click="openAbsenModal(openDay)" class="inline-flex items-center gap-1 text-sm text-green-600 dark:text-green-400 hover:underline">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    {{ __('Present') }}
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Modal absen --}}
            <div x-show="showAbsenModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.self="closeAbsenModal()">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-4 max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Absen Manual') }}</h3>
                        <button type="button" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeAbsenModal()" aria-label="{{ __('Tutup') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                    <template x-if="showAbsenModal && absenDate">
                        <div>
                            <div x-show="!absenSettingsConfigured" class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                                <p class="text-sm text-amber-800 dark:text-amber-200">{{ __('Atur ID User dan Token di') }} <a href="{{ route('settings.absen') }}" class="underline font-medium">{{ __('Pengaturan Absensi') }}</a> {{ __('terlebih dahulu.') }}</p>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2"><span class="font-medium">{{ __('Tanggal') }}:</span> <span x-text="formatDate(absenDate)"></span></p>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Tipe') }}</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" x-model="absenType" value="WFA" class="text-blue-600">
                                        <span>WFA</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" x-model="absenType" value="WFO" class="text-blue-600">
                                        <span>WFO</span>
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Lokasi') }}</label>
                                <div class="flex gap-2 mb-2">
                                    <select x-model="selectedLocationIndex" @change="onLocationSelect(); if (selectedLocationIndex === 'map') $nextTick(() => initMap())" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block flex-1 p-2">
                                        <option value="">-- {{ __('Pilih lokasi tersimpan') }} --</option>
                                        <template x-for="(loc, i) in savedLocations" :key="i">
                                            <option :value="i" x-text="loc.name"></option>
                                        </template>
                                        <option value="map">{{ __('Pilih di peta') }}</option>
                                    </select>
                                </div>
                                <div x-show="selectedLocationIndex === 'map' || (selectedLocationIndex === '' && savedLocations.length === 0)" class="space-y-2">
                                    <div class="relative">
                                        <div id="absen-map" class="h-48 rounded-lg border border-gray-200 dark:border-gray-600" x-ref="mapContainer"></div>
                                        <button type="button" @click="getCurrentLocation()" :disabled="locationLoading" class="absolute bottom-2 right-2 inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 shadow-lg" title="{{ __('Lokasi saat ini') }}">
                                            <svg x-show="!locationLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <svg x-show="locationLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <span x-text="locationLoading ? '{{ __('Memuat...') }}' : '{{ __('Lokasi saat ini') }}'"></span>
                                        </button>
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <input type="text" x-model="newLocationName" placeholder="{{ __('Nama lokasi (untuk simpan)') }}" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block flex-1 p-2">
                                        <button type="button" @click="saveCurrentLocation()" class="px-3 py-2 text-sm font-medium rounded-lg bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-500">
                                            {{ __('Simpan lokasi') }}
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-show="mapLat && mapLng" x-text="'Lat: ' + mapLat + ', Lng: ' + mapLng"></p>
                                </div>
                                <p x-show="absenError" class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="absenError"></p>
                            </div>
                            <div x-show="!canSubmitAbsen()" class="mb-4 p-3 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm">
                                {{ __('Absensi untuk tanggal ini sudah mencapai batas maksimal 2 kali sehari.') }}
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="closeAbsenModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500">
                                    {{ __('Batal') }}
                                </button>
                                <button type="button" @click="submitAbsen()" :disabled="absenSubmitting || !canSubmitAbsen()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    <span x-show="!absenSubmitting">{{ __('Kirim Absen') }}</span>
                                    <span x-show="absenSubmitting">{{ __('Mengirim...') }}</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Pengingat Show your presence untuk hari ini --}}
            @if($absenSettingsConfigured ?? false)
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-4 text-center">
                    <p class="text-amber-800 dark:text-amber-200 font-medium">{{ __('Show your presence') }}</p>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">{{ __('Don\'t forget to check in today.') }}</p>
                    <button type="button" @click="openAbsenModal(todayDate)" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-amber-800 dark:text-amber-200 hover:underline">
                        {{ __('Time Is Money') }}
                    </button>
                </div>
            @endif

            {{-- Agenda bulan ini (satu baris per event; event rentang tanggal tampil sekali dengan rentang) --}}
            @if(!empty($agendaItems))
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">{{ __('Agenda') }} {{ $calendarTitle }}</h2>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($agendaItems as $ev)
                            @php
                                $dateStart = \Carbon\Carbon::parse($ev['date_start']);
                                $dateEnd = \Carbon\Carbon::parse($ev['date_end']);
                                $isRange = $ev['date_start'] !== $ev['date_end'];
                                $dateLabel = $isRange
                                    ? $dateStart->translatedFormat('d M') . ' – ' . $dateEnd->translatedFormat('d M Y')
                                    : $dateStart->translatedFormat('l, d M Y');
                            @endphp
                            <div class="flex items-start gap-2 text-sm">
                                <span class="shrink-0 text-xs font-medium text-gray-500 dark:text-gray-400 w-36">{{ $dateLabel }}</span>
                                @if($ev['type'] === 'holiday')
                                    <span class="shrink-0 px-1.5 py-0.5 text-xs rounded bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">{{ __('Libur') }}</span>
                                    <span class="text-gray-800 dark:text-gray-200 min-w-0">{{ $ev['title'] }}</span>
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
                                    <a href="{{ $ev['url'] }}" class="text-blue-600 dark:text-blue-400 hover:underline truncate min-w-0 flex-1">{{ $ev['title'] }}</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
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
            Alpine.data('dashboardCalendar', (eventsByDate = {}, dashboardParams = {}, absenSettingsConfigured = false, savedLocations = [], todayDate = '', absenByDate = {}) => ({
                openDay: null,
                eventsByDate,
                dashboardParams,
                absenSettingsConfigured,
                savedLocations,
                todayDate,
                absenByDate,
                showAbsenModal: false,
                absenDate: null,
                absenType: 'WFA',
                selectedLocationIndex: '',
                mapLat: null,
                mapLng: null,
                newLocationName: '',
                absenError: '',
                absenSubmitting: false,
                locationLoading: false,
                mapInstance: null,
                mapMarker: null,

                openAbsenModal(date) {
                    this.absenDate = date;
                    this.showAbsenModal = true;
                    this.absenError = '';
                    this.selectedLocationIndex = this.savedLocations.length > 0 ? '0' : 'map';
                    this.mapLat = null;
                    this.mapLng = null;
                    this.$nextTick(() => this.initMap());
                },
                canSubmitAbsen() {
                    const count = this.absenByDate[this.absenDate] ?? 0;
                    return count < 2;
                },
                closeAbsenModal() {
                    this.showAbsenModal = false;
                    this.absenDate = null;
                    this.destroyMap();
                },
                initMap() {
                    if (!window.L || !this.$refs.mapContainer) return;
                    const container = this.$refs.mapContainer;
                    if (this.mapInstance) {
                        this.mapInstance.remove();
                    }
                    const defaultLat = -6.295123;
                    const defaultLng = 106.860504;
                    const lat = this.mapLat ?? defaultLat;
                    const lng = this.mapLng ?? defaultLng;
                    this.mapInstance = window.L.map(container).setView([lat, lng], 14);
                    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.mapInstance);
                    this.mapMarker = window.L.marker([lat, lng]).addTo(this.mapInstance);
                    this.mapInstance.on('click', (e) => {
                        this.mapLat = e.latlng.lat;
                        this.mapLng = e.latlng.lng;
                        if (this.mapMarker) this.mapMarker.setLatLng(e.latlng);
                    });
                },
                destroyMap() {
                    if (this.mapInstance) {
                        this.mapInstance.remove();
                        this.mapInstance = null;
                        this.mapMarker = null;
                    }
                },
                onLocationSelect() {
                    const idx = this.selectedLocationIndex;
                    if (idx !== 'map' && idx !== '' && this.savedLocations[idx]) {
                        const loc = this.savedLocations[idx];
                        this.mapLat = loc.latitude;
                        this.mapLng = loc.longitude;
                    }
                },
                getCurrentLocation() {
                    if (!navigator.geolocation) {
                        this.absenError = '{{ __("Geolokasi tidak didukung oleh browser Anda.") }}';
                        return;
                    }
                    this.locationLoading = true;
                    this.absenError = '';
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.mapLat = pos.coords.latitude;
                            this.mapLng = pos.coords.longitude;
                            if (this.mapInstance && this.mapMarker) {
                                const latlng = [this.mapLat, this.mapLng];
                                this.mapInstance.setView(latlng, 16);
                                this.mapMarker.setLatLng(latlng);
                            }
                            this.locationLoading = false;
                        },
                        () => {
                            this.absenError = '{{ __("Tidak dapat mengambil lokasi. Pastikan izin lokasi diaktifkan.") }}';
                            this.locationLoading = false;
                        }
                    );
                },
                async saveCurrentLocation() {
                    if (!this.mapLat || !this.mapLng) {
                        this.absenError = '{{ __("Klik di peta untuk memilih lokasi terlebih dahulu.") }}';
                        return;
                    }
                    const name = this.newLocationName || ('Lokasi ' + (this.savedLocations.length + 1));
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    try {
                        const res = await fetch('{{ route("absen.locations.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ name, latitude: this.mapLat, longitude: this.mapLng })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.savedLocations = data.locations;
                            this.selectedLocationIndex = String(this.savedLocations.length - 1);
                            this.newLocationName = '';
                            this.absenError = '';
                        }
                    } catch (e) {
                        this.absenError = '{{ __("Gagal menyimpan lokasi.") }}';
                    }
                },
                getCurrentLatLng() {
                    const idx = this.selectedLocationIndex;
                    if (idx === 'map' || idx === '') {
                        return this.mapLat && this.mapLng ? { lat: this.mapLat, lng: this.mapLng } : null;
                    }
                    const loc = this.savedLocations[parseInt(idx, 10)];
                    return loc ? { lat: loc.latitude, lng: loc.longitude } : null;
                },
                async submitAbsen() {
                    const coords = this.getCurrentLatLng();
                    if (!coords) {
                        this.absenError = '{{ __("Pilih lokasi terlebih dahulu (dari daftar atau klik di peta).") }}';
                        return;
                    }
                    this.absenError = '';
                    this.absenSubmitting = true;
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    try {
                        const res = await fetch('{{ route("absen.submit") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                tgl_absen: this.absenDate,
                                longitude: coords.lng,
                                latitude: coords.lat,
                                type: this.absenType,
                                keterangan: this.absenType.toLowerCase()
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.closeAbsenModal();
                            this.openDay = null;
                            window.location.reload();
                        } else {
                            this.absenError = data.message || '{{ __("Gagal mengirim absensi.") }}';
                        }
                    } catch (e) {
                        this.absenError = '{{ __("Gagal mengirim absensi.") }}';
                    } finally {
                        this.absenSubmitting = false;
                    }
                },
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

@extends('layouts.admin')

@section('title', __('Dashboard'))

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">{{ __('Dashboard') }}</h1>
        <p class="text-gray-600">{{ __('Pintasan Anda — klik untuk membuka di tab baru') }}</p>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @forelse($shortcuts as $shortcut)
            <a href="{{ $shortcut->url }}" target="_blank" rel="noopener noreferrer"
                class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                @if($shortcut->icon)
                    <span class="text-3xl mb-2 block">{{ $shortcut->icon }}</span>
                @else
                    <svg class="w-10 h-10 text-gray-400 mb-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                    </svg>
                @endif
                <h3 class="font-semibold text-gray-900 truncate">{{ $shortcut->title }}</h3>
                <p class="text-xs text-gray-500 truncate mt-1">{{ $shortcut->url }}</p>
            </a>
        @empty
            <div class="col-span-full p-6 bg-white border border-gray-200 rounded-lg text-center text-gray-500">
                <p>{{ __('Belum ada pintasan.') }}</p>
                <a href="{{ route('shortcuts.create') }}" class="inline-flex mt-2 text-blue-600 hover:underline">{{ __('Tambah pintasan') }}</a>
            </div>
        @endforelse
    </div>
@endsection

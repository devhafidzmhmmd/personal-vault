@extends('layouts.admin')

@section('title', __('Tambah Event'))

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ __('Tambah Event') }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <form action="{{ route('custom-events.store') }}" method="POST">
            @csrf
            @if(request()->has('year'))<input type="hidden" name="year" value="{{ request('year') }}">@endif
            @if(request()->has('month'))<input type="hidden" name="month" value="{{ request('month') }}">@endif
            <div class="mb-4">
                <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Judul') }}</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('title')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="event_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Tanggal mulai') }}</label>
                <input type="date" name="event_date" id="event_date" value="{{ old('event_date', $date) }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('event_date')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="event_end_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Tanggal selesai') }}</label>
                <input type="date" name="event_end_date" id="event_end_date" value="{{ old('event_end_date', $date) }}"
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="{{ __('Kosongkan = sama dengan tanggal mulai') }}">
                @error('event_end_date')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Kosongkan untuk event satu hari. Isi untuk rentang tanggal.') }}</p>
            </div>
            <div class="mb-4 flex items-center">
                <input type="hidden" name="is_special" value="0">
                <input type="checkbox" name="is_special" id="is_special" value="1" {{ old('is_special') ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="is_special" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Tandai sebagai event spesial (muncul di semua workspace)') }}</label>
                @error('is_special')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Keterangan') }}</label>
                <textarea name="description" id="description" rows="3" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Simpan') }}
                </button>
                <a href="{{ route('dashboard', request()->only('year', 'month')) }}" class="text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Batal') }}
                </a>
            </div>
        </form>
    </div>
@endsection

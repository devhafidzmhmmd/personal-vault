@extends('layouts.admin')

@section('title', __('Prefix'))

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">{{ __('Prefix') }}</h1>
        <a href="{{ route('password-prefixes.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
            {{ __('Tambah Prefix') }}
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 dark:text-green-200 rounded-lg bg-green-50 dark:bg-gray-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3">{{ __('Nama') }}</th>
                    <th class="px-6 py-3">{{ __('Pintasan') }}</th>
                    <th class="px-6 py-3">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prefixes as $prefix)
                    <tr class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $prefix->name }}</td>
                        <td class="px-6 py-4">
                            @if($prefix->shortcut)
                                <a href="{{ $prefix->shortcut->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:underline truncate max-w-xs block">{{ $prefix->shortcut->title }}</a>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('password-prefixes.edit', $prefix) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Edit') }}</a>
                            <form action="{{ route('password-prefixes.destroy', $prefix) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('{{ __('Hapus prefix ini?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">{{ __('Hapus') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('Belum ada prefix.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

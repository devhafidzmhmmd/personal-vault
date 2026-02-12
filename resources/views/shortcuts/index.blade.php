@extends('layouts.admin')

@section('title', __('Pintasan'))

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-semibold text-gray-800">{{ __('Pintasan') }}</h1>
        <a href="{{ route('shortcuts.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
            {{ __('Tambah Pintasan') }}
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">{{ __('Judul') }}</th>
                    <th class="px-6 py-3">{{ __('URL') }}</th>
                    <th class="px-6 py-3">{{ __('Workspace') }}</th>
                    <th class="px-6 py-3">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shortcuts as $shortcut)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $shortcut->title }}</td>
                        <td class="px-6 py-4 truncate max-w-xs">{{ $shortcut->url }}</td>
                        <td class="px-6 py-4">{{ $workspace->name ?? ($shortcut->workspace?->name ?? '—') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('shortcuts.edit', $shortcut) }}" class="text-blue-600 hover:underline">{{ __('Edit') }}</a>
                            <form action="{{ route('shortcuts.destroy', $shortcut) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('{{ __('Hapus pintasan ini?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">{{ __('Hapus') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">{{ __('Belum ada pintasan.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@extends('layouts.settings')

@section('title', 'Edit Workspace')

@section('settings_content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Edit Workspace</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <form action="{{ route('settings.workspace.update', $workspace) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Workspace</label>
                <input type="text" name="name" id="name" value="{{ old('name', $workspace->name) }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4 flex items-center">
                <input type="hidden" name="proman_enabled" value="0">
                <input type="checkbox" name="proman_enabled" id="proman_enabled" value="1"
                    {{ old('proman_enabled', $workspace->proman_enabled) ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="proman_enabled" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Aktifkan Proman</label>
                @error('proman_enabled')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2 flex-wrap items-center">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                    Simpan
                </button>
                <a href="{{ route('settings.workspace.index') }}" class="text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 font-medium rounded-lg text-sm px-4 py-2">
                    Batal
                </a>
                @if($workspace->proman_enabled && session('current_workspace_id') == $workspace->id)
                    <a href="{{ route('settings.workspace.proman.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                        Kelola project Proman
                    </a>
                @endif
            </div>
        </form>
    </div>
@endsection

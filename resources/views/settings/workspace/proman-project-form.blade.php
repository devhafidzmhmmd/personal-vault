@extends('layouts.settings')

@section('title', $project ? __('Edit Project Proman') : __('Tambah Project Proman'))

@section('settings_content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">{{ $project ? __('Edit Project Proman') : __('Tambah Project Proman') }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <form action="{{ $project ? route('settings.workspace.proman.update', $project) : route('settings.workspace.proman.store') }}" method="POST">
            @csrf
            @if($project)@method('PUT')@endif
            <div class="mb-4">
                <label for="id_project" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('ID Project (UUID)') }}</label>
                <input type="text" name="id_project" id="id_project" value="{{ old('id_project', $project?->id_project) }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="uuid-project-id">
                @error('id_project')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Nama') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $project?->name) }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Simpan') }}
                </button>
                <a href="{{ route('settings.workspace.proman.index') }}" class="text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Batal') }}
                </a>
            </div>
        </form>
    </div>
@endsection

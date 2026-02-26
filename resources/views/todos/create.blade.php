@extends('layouts.admin')

@section('title', __('Tambah Todo'))

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ __('Tambah Todo') }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <form action="{{ route('todos.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Judul') }}</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('title')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Deskripsi') }}</label>
                <textarea name="description" id="description" rows="3" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="shortcut_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Pintasan') }}</label>
                <select name="shortcut_id" id="shortcut_id" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">{{ __('— Tidak ada —') }}</option>
                    @foreach($shortcuts as $s)
                        <option value="{{ $s->id }}" {{ old('shortcut_id') == $s->id ? 'selected' : '' }}>{{ $s->title }}</option>
                    @endforeach
                </select>
                @error('shortcut_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Status') }}</label>
                <select name="status" id="status" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    @foreach(\App\Models\Todo::statuses() as $value => $label)
                        <option value="{{ $value }}" {{ old('status', 'todo') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="due_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Jatuh tempo') }}</label>
                <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}"
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('due_date')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Simpan') }}
                </button>
                <a href="{{ route('todos.index') }}" class="text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Batal') }}
                </a>
            </div>
        </form>
    </div>
@endsection

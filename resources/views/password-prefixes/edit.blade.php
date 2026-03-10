@extends('layouts.admin')

@section('title', __('Edit Prefix'))

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">{{ __('Edit Prefix') }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <form action="{{ route('password-prefixes.update', $passwordPrefix) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Nama') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $passwordPrefix->name) }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="shortcut_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Pintasan') }}</label>
                <select name="shortcut_id" id="shortcut_id" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="">{{ __('— Tanpa pintasan') }}</option>
                    @foreach($shortcuts as $s)
                        <option value="{{ $s->id }}" {{ old('shortcut_id', $passwordPrefix->shortcut_id) == $s->id ? 'selected' : '' }}>{{ $s->title }}</option>
                    @endforeach
                </select>
                @error('shortcut_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Simpan') }}
                </button>
                <a href="{{ route('password-prefixes.index') }}" class="text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Batal') }}
                </a>
            </div>
        </form>
    </div>
@endsection

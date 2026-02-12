@extends('layouts.admin')

@section('title', __('Tambah Pintasan'))

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">{{ __('Tambah Pintasan') }}</h1>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <form action="{{ route('shortcuts.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="title" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Judul') }}</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="url" class="block mb-2 text-sm font-medium text-gray-900">{{ __('URL') }}</label>
                <input type="url" name="url" id="url" value="{{ old('url') }}" required placeholder="https://"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @include('shortcuts.partials.icon-picker', ['value' => old('icon')])
            <div class="flex gap-2">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Simpan') }}
                </button>
                <a href="{{ route('shortcuts.index') }}" class="text-gray-700 bg-gray-200 hover:bg-gray-300 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Batal') }}
                </a>
            </div>
        </form>
    </div>
@endsection

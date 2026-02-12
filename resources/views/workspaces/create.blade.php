@extends('layouts.admin')

@section('title', __('Tambah Workspace'))

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">{{ __('Tambah Workspace') }}</h1>
    </div>

    @if(session('info'))
        <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50">{{ session('info') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg p-6 max-w-lg">
        <form action="{{ route('workspaces.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Nama Workspace') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Simpan') }}
                </button>
                <a href="{{ route('workspaces.index') }}" class="text-gray-700 bg-gray-200 hover:bg-gray-300 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Batal') }}
                </a>
            </div>
        </form>
    </div>
@endsection

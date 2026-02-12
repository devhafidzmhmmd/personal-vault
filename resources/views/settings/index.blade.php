@extends('layouts.admin')

@section('title', __('Settings'))

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">{{ __('Settings') }}</h1>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg p-6 max-w-lg">
        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Ganti Master Password') }}</h2>
        <p class="text-sm text-gray-600 mb-4">{{ __('Semua password tersimpan akan dienkripsi ulang dengan master password baru.') }}</p>

        <form action="{{ route('settings.master-password') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="current_password" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Master Password Saat Ini') }}</label>
                <input type="password" name="current_password" id="current_password" required
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Master Password Baru') }}</label>
                <input type="password" name="password" id="password" required
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Konfirmasi Master Password Baru') }}</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                {{ __('Simpan Master Password Baru') }}
            </button>
        </form>
    </div>
@endsection

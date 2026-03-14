@extends('layouts.settings')

@section('title', __('Pengaturan Proman'))

@section('settings_content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">{{ __('Pengaturan Proman') }}</h1>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 dark:text-green-200 rounded-lg bg-green-50 dark:bg-gray-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Kredensial Proman') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('Atur username, password (untuk login SSO), dan API Key (untuk header token saat submit task).') }}</p>

        <form action="{{ route('settings.proman.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="proman_username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Username (email)') }}</label>
                <input type="text" name="proman_username" id="proman_username" value="{{ old('proman_username', $promanUsername) }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="user@example.com">
                @error('proman_username')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="proman_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Password (untuk login SSO)') }}</label>
                <input type="password" name="proman_password" id="proman_password"
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="{{ __('Kosongkan jika tidak ingin mengubah') }}"
                    autocomplete="off">
                @error('proman_password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="proman_api_key" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('API Key') }}</label>
                <input type="text" name="proman_api_key" id="proman_api_key" value="{{ old('proman_api_key', $promanApiKey) }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="{{ __('API Key untuk header token submit') }}">
                @error('proman_api_key')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                {{ __('Simpan') }}
            </button>
        </form>
    </div>
@endsection

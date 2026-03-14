@extends('layouts.settings')

@section('title', __('Pengaturan Absensi'))

@section('settings_content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">{{ __('Pengaturan Absensi') }}</h1>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 dark:text-green-200 rounded-lg bg-green-50 dark:bg-gray-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Integrasi Proman') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('Atur ID User dan Token untuk mengirim absensi ke sistem Proman.') }}</p>

        <form action="{{ route('settings.absen.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="proman_user_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('ID User Proman') }}</label>
                <input type="text" name="proman_user_id" id="proman_user_id" value="{{ old('proman_user_id', $promanUserId) }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="c9b8b818-b6bc-4b88-9b04-f25cb2c92df9">
                @error('proman_user_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="proman_token" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Token API') }}</label>
                <input type="password" name="proman_token" id="proman_token" value="{{ old('proman_token', $promanToken) }}" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="{{ __('Token') }}"
                    autocomplete="off">
                @error('proman_token')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                {{ __('Simpan') }}
            </button>
        </form>
    </div>
@endsection

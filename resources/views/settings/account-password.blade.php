@extends('layouts.settings')

@section('title', __('Ganti Password Akun'))

@section('settings_content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">{{ __('Ganti Password Akun') }}</h1>
    </div>

    @if(session('status') === 'password-updated')
        <div class="p-4 mb-4 text-sm text-green-800 dark:text-green-200 rounded-lg bg-green-50 dark:bg-gray-800">{{ __('Password berhasil diubah.') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Ubah password untuk login ke aplikasi') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('Gunakan password yang kuat dan unik. Ini adalah password yang Anda pakai untuk masuk ke akun.') }}</p>

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            @method('put')
            <div class="mb-4">
                <label for="update_password_current_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Password saat ini') }}</label>
                <input type="password" name="current_password" id="update_password_current_password" required autocomplete="current-password"
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('current_password', 'updatePassword')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="update_password_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Password baru') }}</label>
                <input type="password" name="password" id="update_password_password" required autocomplete="new-password"
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('password', 'updatePassword')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="update_password_password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Konfirmasi password baru') }}</label>
                <input type="password" name="password_confirmation" id="update_password_password_confirmation" required autocomplete="new-password"
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                {{ __('Simpan password baru') }}
            </button>
        </form>
    </div>
@endsection

@extends('layouts.admin')

@section('title', __('Edit Password'))

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">{{ __('Edit Password') }}</h1>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <form action="{{ route('passwords.update', $password) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="type" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Tipe') }}</label>
                <select name="type" id="type" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    @foreach(\App\Models\Password::types() as $value => $label)
                        <option value="{{ $value }}" {{ old('type', $password->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Nama') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $password->name) }}" required
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="username" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Username') }}</label>
                <input type="text" name="username" id="username" value="{{ old('username', $password->username) }}"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <div class="mb-4">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Password (kosongkan jika tidak diubah)') }}</label>
                <div class="flex gap-2">
                    <input type="text" name="password" id="password" placeholder="••••••••" autocomplete="off"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 flex-1 p-2.5 font-mono">
                    <button type="button" id="generate-password" class="shrink-0 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400">
                        {{ __('Generate') }}
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="url" class="block mb-2 text-sm font-medium text-gray-900">{{ __('URL') }}</label>
                <input type="url" name="url" id="url" value="{{ old('url', $password->url) }}"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            </div>
            <div class="mb-4">
                <label for="notes" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Catatan') }}</label>
                <textarea name="notes" id="notes" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">{{ old('notes', $password->notes) }}</textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Simpan') }}
                </button>
                <a href="{{ route('passwords.index') }}" class="text-gray-700 bg-gray-200 hover:bg-gray-300 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Batal') }}
                </a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('generate-password').addEventListener('click', function() {
            var length = 20;
            var upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            var lower = 'abcdefghijklmnopqrstuvwxyz';
            var numbers = '0123456789';
            var all = upper + lower + numbers;
            var password = '';
            password += upper[Math.floor(Math.random() * upper.length)];
            password += lower[Math.floor(Math.random() * lower.length)];
            password += numbers[Math.floor(Math.random() * numbers.length)];
            for (var i = 3; i < length; i++) {
                password += all[Math.floor(Math.random() * all.length)];
            }
            password = password.split('').sort(function() { return Math.random() - 0.5; }).join('');
            document.getElementById('password').value = password;
        });
    </script>
@endsection

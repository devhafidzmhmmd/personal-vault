<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Atur master password untuk mengenkripsi data password Anda. Master password ini diperlukan setiap kali Anda membuka aplikasi.') }}
    </div>

    <form method="POST" action="{{ route('master-password.store') }}">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Master Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus
                autocomplete="new-password" placeholder="{{ __('Masukkan master password') }}" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Master Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password"
                placeholder="{{ __('Ulangi master password') }}" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('Set Master Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

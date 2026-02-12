<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Masukkan master password untuk membuka vault.') }}
    </div>

    <form method="POST" action="{{ route('vault.unlock.store') }}">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Master Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus
                autocomplete="current-password" placeholder="{{ __('Master password') }}" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('Buka Vault') }}
            </x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 underline">
            {{ __('Log out') }}
        </button>
    </form>
</x-guest-layout>

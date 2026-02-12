@props(['value' => ''])
@php
    $value = $value ?? '';
    $emojis = [
        '🔗', '🏠', '⚙️', '⭐', '📧', '📄', '📁', '🔒', '🔑', '🌐', '📱', '💼', '🎵', '🎬', '🛒', '📊',
        '🔍', '✏️', '📌', '🚀', '✅', '❤️', '💡', '🎯', '📞', '🏢', '🏦', '🎨', '🛠️', '📦', '🔔', '📋',
        '🗂️', '📂', '📅', '🕐', '🌟', '🔐', '📲', '💻', '🖥️', '⚡', '🎮', '📷', '🗄️', '📈', '🏷️', '🌍',
        '📌', '🔖', '📝', '🗃️', '💾', '📤', '📥', '🔄', '➕', '➖', '❌', '✔️', '⚠️', '💬', '📢', '🎤',
    ];
@endphp

<div class="mb-4" x-data="{ iconValue: @js($value) }">
    <label for="icon" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Icon (opsional)') }}</label>
    <div class="flex gap-2 items-center flex-wrap">
        <input type="text" name="icon" id="icon" x-model="iconValue" maxlength="10"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 flex-1 min-w-[8rem] p-2.5"
            placeholder="{{ __('Pilih atau ketik emoji') }}">
        <button type="button" @click="$refs.iconPickerModal.classList.remove('hidden'); $refs.iconPickerBackdrop.classList.remove('hidden')"
            class="shrink-0 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400">
            {{ __('Pilih icon') }}
        </button>
        <button type="button" x-show="iconValue" x-cloak @click="iconValue = ''"
            class="shrink-0 px-4 py-2.5 text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-lg">
            {{ __('Hapus') }}
        </button>
    </div>
    <p x-show="iconValue" x-cloak class="mt-1.5 text-sm text-gray-500">
        {{ __('Preview') }}: <span class="text-2xl" x-text="iconValue"></span>
    </p>
    @error('icon')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    {{-- Modal --}}
    <div x-ref="iconPickerModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/50 transition-opacity" x-ref="iconPickerBackdrop"
                @click="$refs.iconPickerModal.classList.add('hidden'); $refs.iconPickerBackdrop.classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-4 max-h-[80vh] flex flex-col">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Pilih icon') }}</h3>
                <div class="grid grid-cols-8 gap-1 overflow-y-auto flex-1 min-h-0">
                    @foreach($emojis as $emoji)
                        <button type="button"
                            class="p-2 text-2xl rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                            @click="iconValue = @js($emoji); $refs.iconPickerModal.classList.add('hidden'); $refs.iconPickerBackdrop.classList.add('hidden')">
                            {{ $emoji }}
                        </button>
                    @endforeach
                </div>
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600 flex justify-end">
                    <button type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 rounded-lg"
                        @click="$refs.iconPickerModal.classList.add('hidden'); $refs.iconPickerBackdrop.classList.add('hidden')">
                        {{ __('Tutup') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

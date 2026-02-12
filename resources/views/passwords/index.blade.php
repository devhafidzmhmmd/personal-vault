@extends('layouts.admin')

@section('title', is_string($t = __('Passwords')) ? $t : 'Passwords')

@section('content')
    @php
        $passwordsLabel = __("Passwords");
        $passwordsLabel = is_array($passwordsLabel) ? ($passwordsLabel[0] ?? "Passwords") : $passwordsLabel;
    @endphp
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-semibold text-gray-800">{{ $passwordsLabel }}</h1>
        <a href="{{ route('passwords.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
            {{ __('Tambah Password') }}
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('passwords.index') }}" class="mb-4 flex flex-wrap gap-2 items-end">
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700">{{ __('Tipe') }}</label>
            <select name="type" id="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2">
                <option value="">{{ __('Semua') }}</option>
                @foreach(\App\Models\Password::types() as $value => $label)
                    <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="text-white bg-gray-700 hover:bg-gray-800 font-medium rounded-lg text-sm px-4 py-2">
            {{ __('Filter') }}
        </button>
    </form>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">{{ __('Nama') }}</th>
                    <th class="px-6 py-3">{{ __('Username') }}</th>
                    <th class="px-6 py-3">{{ __('Tipe') }}</th>
                    <th class="px-6 py-3">{{ __('Password') }}</th>
                    <th class="px-6 py-3">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($passwords as $password)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $password->name }}</td>
                        <td class="px-6 py-4">{{ $password->username ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded {{ match($password->type) { 'app' => 'bg-blue-100 text-blue-800', 'db' => 'bg-green-100 text-green-800', 'server' => 'bg-amber-100 text-amber-800', default => 'bg-gray-100 text-gray-800' } }}">
                                {{ \App\Models\Password::types()[$password->type] ?? $password->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-gray-500 select-none">••••••••</code>
                            <button type="button" data-reveal-url="{{ route('passwords.reveal', $password) }}" class="copy-password ml-2 text-blue-600 hover:underline text-xs">
                                {{ __('Copy') }}
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('passwords.edit', $password) }}" class="text-blue-600 hover:underline">{{ __('Edit') }}</a>
                            <form action="{{ route('passwords.destroy', $password) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('{{ __('Hapus password ini?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">{{ __('Hapus') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">{{ __('Belum ada password.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        document.querySelectorAll('.copy-password').forEach(btn => {
            btn.addEventListener('click', async function() {
                const url = this.dataset.revealUrl;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token || '' } });
                    const data = await res.json();
                    await navigator.clipboard.writeText(data.password);
                    const orig = this.textContent;
                    this.textContent = '{{ __("Tersalin!") }}';
                    setTimeout(() => { this.textContent = orig; }, 1500);
                } catch (e) {
                    alert('{{ __("Gagal menyalin.") }}');
                }
            });
        });
    </script>
@endsection

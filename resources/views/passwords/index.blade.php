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
                            <span class="password-cell flex items-center gap-2 flex-wrap">
                                <code class="password-masked text-gray-500 select-none font-mono">••••••••</code>
                                <code class="password-plain hidden font-mono text-gray-900 dark:text-gray-100 select-all" data-plain=""></code>
                                <span class="flex items-center gap-1">
                                    <button type="button" data-reveal-url="{{ route('passwords.reveal', $password) }}" class="toggle-password text-gray-600 hover:underline text-xs" data-show-label="{{ __('Tampilkan') }}" data-hide-label="{{ __('Sembunyikan') }}">{{ __('Tampilkan') }}</button>
                                    <button type="button" data-reveal-url="{{ route('passwords.reveal', $password) }}" class="copy-password text-blue-600 hover:underline text-xs">{{ __('Copy') }}</button>
                                </span>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $detailJson = json_encode([
                                    'id' => $password->id,
                                    'name' => $password->name,
                                    'username' => $password->username ?? '',
                                    'type' => $password->type,
                                    'typeLabel' => \App\Models\Password::types()[$password->type] ?? $password->type,
                                    'url' => $password->url ?? '',
                                    'notes' => $password->notes ?? '',
                                    'revealUrl' => route('passwords.reveal', $password),
                                    'editUrl' => route('passwords.edit', $password),
                                ]);
                            @endphp
                            <button type="button"
                                class="password-detail-btn text-gray-600 hover:underline text-xs mr-2 cursor-pointer"
                                data-detail="{!! str_replace(['"', '&'], ['&quot;', '&amp;'], $detailJson) !!}">
                                {{ __('Detail') }}
                            </button>
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

    {{-- Modal detail password (plain JS, no Alpine) --}}
    <div id="password-detail-backdrop" class="hidden fixed inset-0 z-40 bg-gray-900/50 transition-opacity"></div>
    <div id="password-detail-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div id="password-detail-panel" class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Detail Password') }}</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Nama') }}</dt>
                        <dd id="detail-name" class="font-medium text-gray-900 dark:text-white mt-0.5"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Username') }}</dt>
                        <dd id="detail-username" class="font-medium text-gray-900 dark:text-white mt-0.5"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Tipe') }}</dt>
                        <dd class="mt-0.5">
                            <span id="detail-type" class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200"></span>
                        </dd>
                    </div>
                    <div id="detail-url-wrap" class="hidden">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('URL') }}</dt>
                        <dd class="mt-0.5">
                            <a id="detail-url" href="#" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:underline break-all"></a>
                        </dd>
                    </div>
                    <div id="detail-notes-wrap" class="hidden">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Catatan') }}</dt>
                        <dd id="detail-notes" class="font-medium text-gray-900 dark:text-white mt-0.5 whitespace-pre-wrap"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Password') }}</dt>
                        <dd class="mt-1 flex items-center gap-2 flex-wrap">
                            <code id="detail-password-masked" class="text-gray-500 select-none font-mono">••••••••</code>
                            <code id="detail-password-plain" class="hidden font-mono text-gray-900 dark:text-gray-100 select-all" data-plain=""></code>
                            <span class="flex items-center gap-1">
                                <button type="button" id="detail-toggle-btn" class="detail-toggle-password text-gray-600 dark:text-gray-400 hover:underline text-xs" data-reveal-url="" data-show-label="{{ __('Tampilkan') }}" data-hide-label="{{ __('Sembunyikan') }}">{{ __('Tampilkan') }}</button>
                                <button type="button" id="detail-copy-btn" class="detail-copy-password text-blue-600 dark:text-blue-400 hover:underline text-xs">{{ __('Copy') }}</button>
                            </span>
                        </dd>
                    </div>
                </dl>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a id="detail-edit-link" href="#" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">{{ __('Edit') }}</a>
                    <button type="button" id="password-detail-close" class="text-gray-700 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 font-medium rounded-lg text-sm px-4 py-2">{{ __('Tutup') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

            function fetchPassword(revealUrl) {
                var body = new URLSearchParams();
                body.append('_token', csrfToken);
                return fetch(revealUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: body.toString()
                }).then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                }).then(function(data) {
                    if (data && typeof data.password !== 'undefined') return data.password;
                    throw new Error('Invalid response');
                });
            }

            function copyToClipboard(text) {
                return navigator.clipboard.writeText(text);
            }

            function openDetailModal(data) {
                document.getElementById('detail-name').textContent = data.name || '';
                document.getElementById('detail-username').textContent = data.username || '—';
                document.getElementById('detail-type').textContent = data.typeLabel || '';
                var urlWrap = document.getElementById('detail-url-wrap');
                var urlEl = document.getElementById('detail-url');
                if (data.url) {
                    urlWrap.classList.remove('hidden');
                    urlEl.href = data.url;
                    urlEl.textContent = data.url;
                } else {
                    urlWrap.classList.add('hidden');
                }
                var notesWrap = document.getElementById('detail-notes-wrap');
                var notesEl = document.getElementById('detail-notes');
                if (data.notes) {
                    notesWrap.classList.remove('hidden');
                    notesEl.textContent = data.notes;
                } else {
                    notesWrap.classList.add('hidden');
                }
                document.getElementById('detail-copy-btn').dataset.revealUrl = data.revealUrl || '';
                var detailToggle = document.getElementById('detail-toggle-btn');
                detailToggle.dataset.revealUrl = data.revealUrl || '';
                detailToggle.textContent = detailToggle.getAttribute('data-show-label') || '{{ __("Tampilkan") }}';
                document.getElementById('detail-password-masked').classList.remove('hidden');
                document.getElementById('detail-password-plain').classList.add('hidden');
                document.getElementById('detail-password-plain').removeAttribute('data-plain');
                document.getElementById('detail-edit-link').href = data.editUrl || '#';
                document.getElementById('password-detail-backdrop').classList.remove('hidden');
                document.getElementById('password-detail-modal').classList.remove('hidden');
            }
            function closeDetailModal() {
                document.getElementById('password-detail-backdrop').classList.add('hidden');
                document.getElementById('password-detail-modal').classList.add('hidden');
            }

            document.addEventListener('click', function(e) {
                var detailBtn = e.target.closest('.password-detail-btn');
                if (detailBtn && detailBtn.dataset.detail) {
                    e.preventDefault();
                    try {
                        var raw = detailBtn.getAttribute('data-detail');
                        var decoded = raw.replace(/&quot;/g, '"').replace(/&amp;/g, '&');
                        var data = JSON.parse(decoded);
                        openDetailModal(data);
                    } catch (err) {
                        console.error('Detail parse error', err);
                    }
                    return;
                }
                if (e.target.id === 'password-detail-close' || e.target.id === 'password-detail-backdrop') {
                    closeDetailModal();
                    return;
                }
                var modal = document.getElementById('password-detail-modal');
                if (modal && !modal.classList.contains('hidden') && e.target.closest('#password-detail-modal') && !e.target.closest('#password-detail-panel')) {
                    closeDetailModal();
                    return;
                }

                var toggleBtn = e.target.closest('.toggle-password, .detail-toggle-password');
                if (toggleBtn && toggleBtn.dataset.revealUrl) {
                    e.preventDefault();
                    var cell = toggleBtn.closest('.password-cell') || document.getElementById('password-detail-panel');
                    var maskedEl = cell ? cell.querySelector('.password-masked, #detail-password-masked') : null;
                    var plainEl = cell ? cell.querySelector('.password-plain, #detail-password-plain') : null;
                    if (!maskedEl || !plainEl) return;
                    if (plainEl.classList.contains('hidden')) {
                        var url = toggleBtn.dataset.revealUrl;
                        var showL = toggleBtn.getAttribute('data-show-label') || '{{ __("Tampilkan") }}';
                        var hideL = toggleBtn.getAttribute('data-hide-label') || '{{ __("Sembunyikan") }}';
                        toggleBtn.disabled = true;
                        fetchPassword(url).then(function(plain) {
                            plainEl.textContent = plain;
                            plainEl.setAttribute('data-plain', plain);
                            plainEl.classList.remove('hidden');
                            maskedEl.classList.add('hidden');
                            toggleBtn.textContent = hideL;
                        }).catch(function() { alert('{{ __("Gagal memuat password.") }}'); }).finally(function() { toggleBtn.disabled = false; });
                    } else {
                        var showL = toggleBtn.getAttribute('data-show-label') || '{{ __("Tampilkan") }}';
                        plainEl.classList.add('hidden');
                        plainEl.textContent = '';
                        plainEl.removeAttribute('data-plain');
                        maskedEl.classList.remove('hidden');
                        toggleBtn.textContent = showL;
                    }
                    return;
                }

                var copyBtn = e.target.closest('.copy-password, .detail-copy-password');
                if (copyBtn && copyBtn.dataset.revealUrl) {
                    e.preventDefault();
                    var cell = copyBtn.closest('.password-cell') || document.getElementById('password-detail-panel');
                    var plainEl = cell ? cell.querySelector('.password-plain, #detail-password-plain') : null;
                    var alreadyRevealed = plainEl && !plainEl.classList.contains('hidden') && plainEl.getAttribute('data-plain');
                    var url = copyBtn.dataset.revealUrl;
                    var p = alreadyRevealed ? Promise.resolve(plainEl.getAttribute('data-plain')) : fetchPassword(url);
                    p.then(function(password) {
                        return copyToClipboard(password);
                    }).then(function() {
                        var orig = copyBtn.textContent;
                        copyBtn.textContent = '{{ __("Tersalin!") }}';
                        setTimeout(function() { copyBtn.textContent = orig; }, 1500);
                    }).catch(function() { alert('{{ __("Gagal menyalin.") }}'); });
                }
            });
        })();
    </script>
@endsection

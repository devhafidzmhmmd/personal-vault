<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <script>
        if (localStorage.theme === 'dark' || (!localStorage.theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <!-- Sidebar toggle for mobile -->
    <div class="fixed top-4 left-4 z-50 sm:hidden">
        <button type="button" data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" class="inline-flex items-center p-2 text-sm text-gray-500 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-600">
            <span class="sr-only">Open sidebar</span>
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path></svg>
        </button>
    </div>

    <aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
        <div class="h-full px-3 py-4 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
            <div class="flex items-center ps-2.5 mb-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <img src="{{ asset('logo.svg') }}" alt="{{ config('app.name') }}" class="h-8 w-8 shrink-0">
                    <span class="self-center text-xl font-semibold whitespace-nowrap text-gray-800 dark:text-white">{{ config('app.name') }}</span>
                </a>
            </div>
            <div class="mb-5 border-y border-gray-200 dark:border-gray-700 pb-2">
                <a href="{{ route('profile.edit') }}" class="flex items-center p-2 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-sm mb-2">
                    <span class="ms-3 truncate">{{ Auth::user()->name }}</span>
                </a>
                {{-- Switch workspace --}}
                @if($currentWorkspace && $sidebarWorkspaces->isNotEmpty())
                    <div class="px-2">
                        <div class="relative" id="workspace-dropdown">
                            <button type="button" class="flex items-center w-full p-2 text-gray-900 dark:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" data-dropdown-toggle="workspace-switch-menu">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path></svg>
                                <span class="ms-2 truncate">{{ $currentWorkspace->name }}</span>
                                <svg class="w-4 h-4 ms-auto text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                            <div id="workspace-switch-menu" class="hidden absolute top-full left-0 mt-1 w-full min-w-[12rem] py-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow">
                                @foreach($sidebarWorkspaces as $ws)
                                    @if($ws->id === $currentWorkspace->id)
                                        <span class="flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600">
                                            <span class="truncate">{{ $ws->name }}</span>
                                            <span class="ms-1 text-gray-400 dark:text-gray-400 text-xs">(aktif)</span>
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('workspace.switch') }}" class="block">
                                            @csrf
                                            <input type="hidden" name="workspace_id" value="{{ $ws->id }}">
                                            <button type="submit" class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                <span class="truncate">{{ $ws->name }}</span>
                                            </button>
                                        </form>
                                    @endif
                                @endforeach
                                <a href="{{ route('settings.workspace.index') }}" class="flex items-center px-3 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    Kelola workspace
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <ul class="space-y-2 font-medium flex-1 overflow-y-auto">
                <li>
                    <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-gray-900 dark:text-white rounded-lg {{ request()->routeIs('dashboard') ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        <span class="ms-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('passwords.index') }}" class="flex items-center p-2 text-gray-900 dark:text-white rounded-lg {{ request()->routeIs('passwords.*') ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                        <span class="ms-3">Passwords</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('shortcuts.index') }}" class="flex items-center p-2 text-gray-900 dark:text-white rounded-lg {{ request()->routeIs('shortcuts.*') ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path><path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path></svg>
                        <span class="ms-3">Pintasan</span>
                    </a>
                </li>
            </ul>
            <div class="mt-auto pt-3 border-t border-gray-200 dark:border-gray-700 space-y-1">
                <button type="button" id="theme-toggle" class="flex items-center w-full p-2 text-gray-900 dark:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Toggle dark mode">
                    <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                    <svg id="theme-toggle-light-icon" class="hidden w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
                    <span class="ms-3" id="theme-toggle-label">Dark mode</span>
                </button>
                <a href="{{ route('settings.index') }}" class="flex items-center w-full p-2 text-gray-900 dark:text-white rounded-lg {{ request()->routeIs('settings.*') ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path></svg>
                    <span class="ms-3">Pengaturan</span>
                </a>
                <form method="POST" action="{{ route('vault.lock') }}" class="w-full">
                    @csrf
                    <button type="submit" class="flex items-center w-full p-2 text-gray-900 dark:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                        <span class="ms-3">Kunci</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="p-4 sm:ml-64 min-h-screen bg-gray-50 dark:bg-gray-900">
        @isset($header)
            <header class="mb-4 text-gray-900 dark:text-white">
                {{ $header }}
            </header>
        @endisset
        <main>
            @yield('content')
        </main>
    </div>

    <script>
        (function() {
            var html = document.documentElement;
            var btn = document.getElementById('theme-toggle');
            var darkIcon = document.getElementById('theme-toggle-dark-icon');
            var lightIcon = document.getElementById('theme-toggle-light-icon');
            var label = document.getElementById('theme-toggle-label');
            function updateUi() {
                var isDark = html.classList.contains('dark');
                darkIcon.classList.toggle('hidden', !isDark);
                lightIcon.classList.toggle('hidden', isDark);
                label.textContent = isDark ? 'Light mode' : 'Dark mode';
            }
            btn.addEventListener('click', function() {
                html.classList.toggle('dark');
                localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
                updateUi();
            });
            updateUi();
        })();
    </script>
</body>
</html>

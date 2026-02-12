@extends('layouts.admin')

@section('content')
    <div class="flex gap-6">
        <div class="flex-1 min-w-0">
            @yield('settings_content')
        </div>
        {{-- Navbar vertikal submenu pengaturan --}}
        <nav class="w-56 shrink-0 border-l border-gray-200 dark:border-gray-700 p-4 mr-6" aria-label="Pengaturan">
            <div class="p-2">
                <p class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengaturan</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('settings.master-password') }}" class="flex items-center px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.master-password') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                            Master Password
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.workspace.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.workspace.*') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path></svg>
                            Workspace
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
@endsection

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
                        <a href="{{ route('settings.account-password') }}" class="flex items-center px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.account-password') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-2 2a5 5 0 00-5 5v2a1 1 0 001 1h12a1 1 0 001-1v-2a5 5 0 00-5-5H8z" clip-rule="evenodd"></path></svg>
                            {{ __('Password akun') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.master-password') }}" class="flex items-center px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.master-password') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Master Password') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.workspace.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.workspace.*') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path></svg>
                            Workspace
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.absen') }}" class="flex items-center px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.absen*') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                            {{ __('Absensi') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.proman') }}" class="flex items-center px-3 py-2 text-sm rounded-lg {{ request()->routeIs('settings.proman*') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                            {{ __('Proman') }}
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
@endsection

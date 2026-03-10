@extends('layouts.admin')

@section('content')
    <div class="flex gap-6">
        <div class="flex-1 min-w-0">
            @yield('tools_content')
        </div>
        <nav class="w-56 shrink-0 border-l border-gray-200 dark:border-gray-700 p-4 mr-6" aria-label="Tools">
            <div class="p-2">
                <p class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tools</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('tools.json-to-excel') }}" class="flex items-center px-3 py-2 text-sm rounded-lg {{ request()->routeIs('tools.json-to-excel*') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586L7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path></svg>
                            JSON to Excel
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
@endsection

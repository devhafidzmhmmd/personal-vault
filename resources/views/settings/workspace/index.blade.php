@extends('layouts.settings')

@section('title', 'Pengaturan Workspace')

@section('settings_content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-semibold text-gray-800">Pengaturan Workspace</h1>
        <a href="{{ route('settings.workspace.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
            Tambah Workspace
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workspaces as $workspace)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $workspace->name }}
                            @if($currentWorkspace && $currentWorkspace->id === $workspace->id)
                                <span class="ml-2 text-xs text-gray-500">(aktif)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if(!$currentWorkspace || $currentWorkspace->id !== $workspace->id)
                                <form action="{{ route('workspace.switch') }}" method="POST" class="inline-block mr-2">
                                    @csrf
                                    <input type="hidden" name="workspace_id" value="{{ $workspace->id }}">
                                    <button type="submit" class="text-blue-600 hover:underline">Gunakan</button>
                                </form>
                            @endif
                            <a href="{{ route('settings.workspace.edit', $workspace) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('settings.workspace.destroy', $workspace) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Hapus workspace dan semua password di dalamnya?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-6 py-8 text-center text-gray-500">Belum ada workspace.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

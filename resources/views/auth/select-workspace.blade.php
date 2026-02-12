@extends('layouts.guest-wide')

@section('content')
    <div class="w-full">
        <p class="text-center text-gray-600 mb-6">
            Pilih workspace untuk melanjutkan. Setelah memilih, Anda akan diminta memasukkan master password.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($workspaces as $workspace)
                <a href="{{ route('workspace.choose', $workspace) }}"
                    class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 hover:border-blue-300 transition text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                    </svg>
                    <h3 class="font-semibold text-gray-900">{{ $workspace->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Klik untuk masuk</p>
                </a>
            @endforeach

            {{-- Card buat workspace baru --}}
            <div class="block p-6 bg-white border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 hover:bg-blue-50/30 transition text-center">
                <button type="button" data-modal-toggle="create-workspace-modal"
                    class="w-full text-left focus:outline-none">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <h3 class="font-semibold text-gray-700">Buat workspace baru</h3>
                    <p class="text-sm text-gray-500 mt-1">Tambah workspace</p>
                </button>
            </div>
        </div>

        @if($workspaces->isEmpty())
            <p class="text-center text-gray-500 mt-4">Belum ada workspace. Klik "Buat workspace baru" untuk membuat yang pertama.</p>
        @endif
    </div>

    {{-- Modal buat workspace --}}
    <div id="create-workspace-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto mx-auto flex items-center justify-center">
            <div class="relative bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Buat workspace baru</h3>
                </div>
                <form action="{{ route('workspace.create-from-select') }}" method="POST" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama workspace</label>
                        <input type="text" name="name" id="name" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            placeholder="Contoh: Kantor, Project A">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" data-modal-toggle="create-workspace-modal"
                            class="text-gray-700 bg-gray-200 hover:bg-gray-300 font-medium rounded-lg text-sm px-4 py-2">
                            Batal
                        </button>
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2">
                            Buat &amp; masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="create-workspace-modal-backdrop" class="hidden bg-gray-900/50 fixed inset-0 z-40"></div>

    <script>
        document.querySelector('[data-modal-toggle="create-workspace-modal"]')?.addEventListener('click', function() {
            document.getElementById('create-workspace-modal').classList.toggle('hidden');
            document.getElementById('create-workspace-modal-backdrop').classList.toggle('hidden');
        });
        document.getElementById('create-workspace-modal-backdrop')?.addEventListener('click', function() {
            document.getElementById('create-workspace-modal').classList.add('hidden');
            document.getElementById('create-workspace-modal-backdrop').classList.add('hidden');
        });
    </script>
@endsection

@extends('layouts.tools')

@section('title', 'JSON to Excel')

@section('tools_content')
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">JSON to Excel</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Upload file JSON untuk dikonversi menjadi file Excel (.xlsx).') }}</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 text-sm text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6"
        x-data="jsonPreview()"
    >
        <form action="{{ route('tools.json-to-excel.convert') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="json_path" :value="selectedPath">

            <div class="mb-4">
                <label for="json_file" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">{{ __('File JSON') }}</label>
                <div class="relative">
                    <label for="json_file" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                        :class="error
                            ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20'
                            : (fileName
                                ? 'border-green-400 dark:border-green-500 bg-green-50 dark:bg-green-900/10 hover:bg-green-100 dark:hover:bg-green-900/20'
                                : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600'
                            )"
                    >
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg x-show="!fileName" class="w-8 h-8 mb-3 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <svg x-show="fileName && !error" x-cloak class="w-8 h-8 mb-3 text-green-500 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <svg x-show="error" x-cloak class="w-8 h-8 mb-3 text-red-500 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="mb-1 text-sm">
                                <span x-show="!fileName" class="font-semibold text-gray-500 dark:text-gray-400">{{ __('Klik untuk upload') }}</span>
                                <span x-show="fileName && !error" x-text="fileName" x-cloak class="font-semibold text-green-600 dark:text-green-400"></span>
                                <span x-show="error" x-text="error" x-cloak class="font-semibold text-red-600 dark:text-red-400"></span>
                            </p>
                            <p x-show="!fileName" class="text-xs text-gray-500 dark:text-gray-400">.json (max 10MB)</p>
                            <p x-show="fileName && !error" x-cloak class="text-xs text-green-600 dark:text-green-400" x-text="totalRows + ' baris, ' + headings.length + ' kolom'"></p>
                        </div>
                        <input type="file" name="json_file" id="json_file" accept=".json" class="hidden"
                            x-on:change="handleFile($event)">
                    </label>
                </div>
                @error('json_file')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Output filename --}}
            <template x-if="isValid">
                <div class="mb-4">
                    <label for="document_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">{{ __('Nama file output') }}</label>
                    <div class="flex items-center">
                        <input type="text" name="document_name" id="document_name" x-model="documentName"
                            class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 text-sm rounded-l-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <span class="inline-flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 border border-l-0 border-gray-300 dark:border-gray-600 rounded-r-lg">.xlsx</span>
                    </div>
                </div>
            </template>

            {{-- Key selector --}}
            <template x-if="arrayKeys.length > 0">
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">{{ __('Pilih data key') }}</label>
                    <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">{{ __('JSON berupa object. Pilih key yang berisi array data untuk dikonversi:') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="k in arrayKeys" :key="k.key">
                            <button type="button"
                                x-on:click="selectPath(k.key)"
                                class="px-3 py-1.5 text-sm rounded-lg border transition-colors flex items-center gap-1.5"
                                :class="selectedPath === k.key
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'
                                    : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                            >
                                <span x-text="k.key"></span>
                                <span class="text-xs opacity-60" x-text="'(' + k.count + ' items)'"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Preview table --}}
            <template x-if="headings.length > 0 && !error">
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ __('Preview') }}</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="previewLabel()"></span>
                    </div>
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
                            <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <template x-for="h in headings" :key="h">
                                        <th class="px-4 py-2 font-medium whitespace-nowrap" x-text="h"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in previewRows" :key="idx">
                                    <tr class="border-t border-gray-200 dark:border-gray-700">
                                        <template x-for="h in headings" :key="h">
                                            <td class="px-4 py-2 whitespace-nowrap max-w-[200px] truncate" x-text="formatCell(row[h])"></td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <div class="flex gap-2">
                <button type="submit"
                    :disabled="!isValid"
                    class="font-medium rounded-lg text-sm px-4 py-2 flex items-center gap-2 transition-colors"
                    :class="isValid
                        ? 'text-white bg-blue-700 hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800'
                        : 'text-gray-400 dark:text-gray-500 bg-gray-200 dark:bg-gray-700 cursor-not-allowed'"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    {{ __('Konversi & Download') }}
                </button>
                <button x-show="fileName" x-cloak type="button" x-on:click="reset()"
                    class="text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 font-medium rounded-lg text-sm px-4 py-2">
                    {{ __('Reset') }}
                </button>
            </div>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-200 mb-2">{{ __('Format JSON yang didukung') }}</h3>
            <div class="text-xs text-gray-500 dark:text-gray-400 space-y-3">
                <div>
                    <p class="font-medium mb-1">{{ __('Array of objects:') }}</p>
                    <pre class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg overflow-x-auto"><code>[
  { "name": "John", "email": "john@example.com" },
  { "name": "Jane", "email": "jane@example.com" }
]</code></pre>
                </div>
                <div>
                    <p class="font-medium mb-1">{{ __('Object dengan nested array (pilih key):') }}</p>
                    <pre class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg overflow-x-auto"><code>{
  "data": [
    { "name": "John", "email": "john@example.com" }
  ],
  "meta": { "total": 1 }
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        function jsonPreview() {
            var maxPreview = 10;

            return {
                fileName: '',
                error: '',
                headings: [],
                previewRows: [],
                totalRows: 0,
                isValid: false,
                documentName: '',
                arrayKeys: [],
                selectedPath: '',
                parsedData: null,

                handleFile(event) {
                    var file = event.target.files[0];
                    this.resetState();

                    if (!file) return;

                    if (!file.name.toLowerCase().endsWith('.json')) {
                        this.error = '{{ __("File harus berformat .json") }}';
                        return;
                    }

                    if (file.size > 10 * 1024 * 1024) {
                        this.error = '{{ __("File terlalu besar (max 10MB)") }}';
                        return;
                    }

                    this.fileName = file.name;
                    this.documentName = file.name.replace(/\.json$/i, '');

                    var reader = new FileReader();
                    var self = this;
                    reader.onload = function(e) {
                        self.parseJson(e.target.result);
                    };
                    reader.onerror = function() {
                        self.error = '{{ __("Gagal membaca file") }}';
                    };
                    reader.readAsText(file);
                },

                parseJson(content) {
                    var parsed;
                    try {
                        parsed = JSON.parse(content);
                    } catch (e) {
                        this.error = '{{ __("JSON tidak valid") }}: ' + e.message;
                        return;
                    }

                    if (typeof parsed !== 'object' || parsed === null) {
                        this.error = '{{ __("JSON harus berisi object atau array of objects") }}';
                        return;
                    }

                    this.parsedData = parsed;

                    if (Array.isArray(parsed)) {
                        this.selectedPath = '';
                        this.loadItems(parsed);
                        return;
                    }

                    var keys = this.findArrayKeys(parsed);
                    if (keys.length === 0) {
                        this.error = '{{ __("Tidak ditemukan array of objects dalam JSON") }}';
                        return;
                    }

                    this.arrayKeys = keys;
                    this.selectPath(keys[0].key);
                },

                findArrayKeys(obj) {
                    var keys = [];
                    for (var key in obj) {
                        if (!obj.hasOwnProperty(key)) continue;
                        var val = obj[key];
                        if (Array.isArray(val) && val.length > 0 && typeof val[0] === 'object' && val[0] !== null && !Array.isArray(val[0])) {
                            keys.push({ key: key, count: val.length });
                        }
                    }
                    return keys;
                },

                selectPath(key) {
                    this.selectedPath = key;
                    this.headings = [];
                    this.previewRows = [];
                    this.totalRows = 0;
                    this.isValid = false;
                    this.error = '';

                    var items = this.parsedData[key];
                    this.loadItems(items);
                },

                loadItems(items) {
                    if (!Array.isArray(items) || items.length === 0) {
                        this.error = '{{ __("Array kosong, tidak ada data") }}';
                        return;
                    }

                    var keys = {};
                    for (var i = 0; i < items.length; i++) {
                        if (typeof items[i] !== 'object' || items[i] === null || Array.isArray(items[i])) {
                            this.error = '{{ __("Setiap item harus berupa object") }}';
                            return;
                        }
                        var itemKeys = Object.keys(items[i]);
                        for (var j = 0; j < itemKeys.length; j++) {
                            keys[itemKeys[j]] = true;
                        }
                    }

                    this.headings = Object.keys(keys);
                    this.totalRows = items.length;
                    this.previewRows = items.slice(0, maxPreview);
                    this.isValid = true;
                },

                formatCell(value) {
                    if (value === null || value === undefined) return '';
                    if (typeof value === 'object') return JSON.stringify(value);
                    return String(value);
                },

                previewLabel() {
                    if (this.totalRows <= maxPreview) {
                        return '{{ __("Menampilkan semua") }} ' + this.totalRows + ' {{ __("baris") }}';
                    }
                    return '{{ __("Menampilkan") }} ' + maxPreview + ' {{ __("dari") }} ' + this.totalRows + ' {{ __("baris") }}';
                },

                reset() {
                    this.resetState();
                    document.getElementById('json_file').value = '';
                },

                resetState() {
                    this.fileName = '';
                    this.error = '';
                    this.headings = [];
                    this.previewRows = [];
                    this.totalRows = 0;
                    this.isValid = false;
                    this.documentName = '';
                    this.arrayKeys = [];
                    this.selectedPath = '';
                    this.parsedData = null;
                }
            };
        }
    </script>
@endsection

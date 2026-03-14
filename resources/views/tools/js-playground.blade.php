@extends('layouts.tools')

@section('title', 'JS Playground')

@section('tools_content')
    <div x-data="jsPlayground()">
        <div class="mb-4">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">JS Playground</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Eksekusi kode JavaScript dan manipulasi data JSON langsung di browser.') }}</p>
        </div>

        {{-- JSON Upload --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">{{ __('Upload JSON (opsional)') }}</label>
                <div class="flex items-center gap-2" x-show="jsonFileName" x-cloak>
                    <span class="text-xs text-green-600 dark:text-green-400 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span x-text="jsonFileName"></span>
                    </span>
                    <button type="button" x-on:click="removeJson()" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">&times; {{ __('Hapus') }}</button>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <label for="json_upload" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg border cursor-pointer transition-colors"
                    :class="jsonFileName
                        ? 'border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30'
                        : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <span x-text="jsonFileName ? '{{ __('Ganti file') }}' : '{{ __('Pilih file JSON') }}'"></span>
                </label>
                <input type="file" id="json_upload" accept=".json" class="hidden" x-on:change="handleJsonUpload($event)">
                <p class="text-xs text-gray-500 dark:text-gray-400" x-show="!jsonFileName">{{ __('File akan tersedia sebagai variabel') }} <code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono">data</code></p>
            </div>
            <p x-show="jsonError" x-cloak class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="jsonError"></p>

            {{-- JSON Summary --}}
            <template x-if="jsonSummary">
                <div class="mt-3 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" x-cloak>
                    <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ __('Ringkasan JSON') }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="px-2 py-0.5 rounded-full font-medium"
                                :class="jsonSummary.rootType === 'Array' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'"
                                x-text="jsonSummary.rootType"></span>
                            <span x-show="jsonSummary.recordCount !== null" class="text-gray-500 dark:text-gray-400" x-text="jsonSummary.recordCount + ' {{ __('record') }}'"></span>
                            <span class="text-gray-400 dark:text-gray-500" x-text="jsonSummary.fileSize"></span>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-white dark:bg-gray-900">
                        <div class="flex gap-6">
                            {{-- Structure tree --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('Struktur') }}</p>
                                <div class="font-mono text-xs leading-relaxed text-gray-700 dark:text-gray-300 overflow-x-auto">
                                    <template x-for="(line, idx) in jsonSummary.structureLines" :key="idx">
                                        <div class="flex">
                                            <span class="text-gray-400 dark:text-gray-600 select-none whitespace-pre" x-text="line.indent"></span>
                                            <span :class="{
                                                'text-purple-600 dark:text-purple-400': line.isKey,
                                                'text-green-600 dark:text-green-400': line.valType === 'string',
                                                'text-blue-600 dark:text-blue-400': line.valType === 'number',
                                                'text-yellow-600 dark:text-yellow-400': line.valType === 'boolean',
                                                'text-gray-500 dark:text-gray-400': line.valType === 'null',
                                                'text-gray-700 dark:text-gray-300': !line.valType && !line.isKey,
                                            }" x-text="line.text"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Fields info (if array of objects) --}}
                            <template x-if="jsonSummary.fields.length > 0">
                                <div class="w-64 shrink-0 border-l border-gray-200 dark:border-gray-700 pl-6">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('Field') }} <span class="normal-case" x-text="'(' + jsonSummary.fields.length + ')'"></span></p>
                                    <div class="space-y-1 max-h-48 overflow-y-auto">
                                        <template x-for="(field, idx) in jsonSummary.fields" :key="idx">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="font-mono text-gray-700 dark:text-gray-300 truncate" x-text="field.name"></span>
                                                <span class="ml-2 shrink-0 px-1.5 py-0.5 rounded text-[10px] font-medium"
                                                    :class="{
                                                        'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400': field.type === 'string',
                                                        'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400': field.type === 'number',
                                                        'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400': field.type === 'boolean',
                                                        'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400': field.type === 'array' || field.type === 'object',
                                                        'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400': field.type === 'mixed' || field.type === 'null',
                                                    }"
                                                    x-text="field.type"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Code Editor --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <label for="code_editor" class="block text-sm font-medium text-gray-900 dark:text-gray-200">{{ __('Kode JavaScript') }}</label>
                <div class="flex items-center gap-2">
                    <button type="button" x-on:click="loadExample()" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">{{ __('Contoh') }}</button>
                    <button type="button" x-on:click="clearCode()" class="text-xs text-gray-500 dark:text-gray-400 hover:underline">{{ __('Kosongkan') }}</button>
                </div>
            </div>
            <div class="relative border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-gray-900">
                <div class="flex">
                    <div class="select-none text-right pr-3 pl-3 py-3 text-xs font-mono leading-[1.625rem] text-gray-500 bg-gray-950 border-r border-gray-800" aria-hidden="true" x-ref="lineNumbers" x-html="lineNumbers"></div>
                    <textarea
                        id="code_editor"
                        x-ref="editor"
                        x-model="code"
                        x-on:input="updateLineNumbers()"
                        x-on:keydown.tab.prevent="handleTab($event)"
                        x-on:keydown.ctrl.enter.prevent="run()"
                        x-on:keydown.meta.enter.prevent="run()"
                        x-on:scroll="syncScroll()"
                        class="w-full bg-transparent text-green-300 font-mono text-sm leading-[1.625rem] p-3 resize-none focus:outline-none focus:ring-0 border-0"
                        style="min-height: 280px; tab-size: 2;"
                        spellcheck="false"
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="off"
                        placeholder="{{ __('Tulis kode JavaScript di sini...') }}"
                    ></textarea>
                </div>
            </div>
            <div class="flex items-center justify-between mt-2">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <kbd class="px-1.5 py-0.5 text-xs font-mono bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded">Ctrl</kbd> + <kbd class="px-1.5 py-0.5 text-xs font-mono bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded">Enter</kbd>
                    {{ __('untuk menjalankan') }}
                </p>
                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="'{{ __('Baris') }}: ' + lineCount"></span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 mb-4">
            <button type="button" x-on:click="run()"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 rounded-lg transition-colors focus:ring-4 focus:ring-green-300 dark:focus:ring-green-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                {{ __('Jalankan') }}
            </button>
            <button type="button" x-on:click="clearOutput()"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                {{ __('Bersihkan Output') }}
            </button>
        </div>

        {{-- Output Console --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">{{ __('Output') }}</label>
                <div class="flex items-center gap-3">
                    <button type="button" x-on:click="copyOutput()" x-show="output.length > 0" x-cloak
                        class="inline-flex items-center gap-1 text-xs transition-colors"
                        :class="copied ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'">
                        <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <svg x-show="copied" x-cloak class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        <span x-text="copied ? '{{ __('Tersalin!') }}' : '{{ __('Salin') }}'"></span>
                    </button>
                    <span class="text-xs font-mono px-2 py-0.5 rounded"
                        :class="executionTime !== null ? 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20' : 'text-gray-400 dark:text-gray-500'"
                        x-show="executionTime !== null" x-cloak
                        x-text="executionTime + 'ms'"></span>
                </div>
            </div>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-900 overflow-hidden">
                <div x-ref="output" class="p-4 font-mono text-sm leading-relaxed overflow-auto" style="min-height: 160px; max-height: 400px;">
                    <template x-if="output.length === 0">
                        <p class="text-gray-500 italic">{{ __('Output akan ditampilkan di sini...') }}</p>
                    </template>
                    <template x-for="(entry, idx) in output" :key="idx">
                        <div class="flex gap-2 mb-0.5"
                            :class="{
                                'text-gray-300': entry.type === 'log',
                                'text-red-400': entry.type === 'error',
                                'text-yellow-400': entry.type === 'warn',
                                'text-blue-400': entry.type === 'info',
                                'text-cyan-400': entry.type === 'return',
                                'text-gray-600': entry.type === 'separator',
                            }">
                            <span class="select-none shrink-0 w-4 text-right text-gray-600" x-show="entry.type !== 'separator'" x-text="entry.type === 'error' ? '✕' : (entry.type === 'warn' ? '⚠' : (entry.type === 'return' ? '←' : '›'))"></span>
                            <span class="whitespace-pre-wrap break-all" x-show="entry.type !== 'separator'" x-text="entry.text"></span>
                            <span class="w-full border-t border-gray-800 my-1" x-show="entry.type === 'separator'"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Available Variables --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-200 mb-2">{{ __('Variabel & Fungsi Tersedia') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-gray-500 dark:text-gray-400">
                <div>
                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Variabel:') }}</p>
                    <ul class="space-y-1">
                        <li><code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded font-mono">data</code> — {{ __('Data JSON yang diupload (null jika tidak ada)') }}</li>
                    </ul>
                </div>
                <div>
                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Fungsi:') }}</p>
                    <ul class="space-y-1">
                        <li><code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded font-mono">console.log()</code> — {{ __('Cetak output') }}</li>
                        <li><code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded font-mono">console.table()</code> — {{ __('Tampilkan tabel') }}</li>
                        <li><code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded font-mono">console.warn()</code> — {{ __('Peringatan') }}</li>
                        <li><code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded font-mono">console.error()</code> — {{ __('Error') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function jsPlayground() {
            return {
                code: '',
                output: [],
                jsonData: null,
                jsonFileName: '',
                jsonError: '',
                jsonSummary: null,
                lineCount: 1,
                lineNumbers: '1',
                executionTime: null,
                copied: false,

                init() {
                    this.updateLineNumbers();
                },

                handleJsonUpload(event) {
                    var file = event.target.files[0];
                    this.jsonError = '';

                    if (!file) return;

                    if (!file.name.toLowerCase().endsWith('.json')) {
                        this.jsonError = '{{ __("File harus berformat .json") }}';
                        event.target.value = '';
                        return;
                    }

                    if (file.size > 50 * 1024 * 1024) {
                        this.jsonError = '{{ __("File terlalu besar (max 50MB)") }}';
                        event.target.value = '';
                        return;
                    }

                    var self = this;
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            self.jsonData = JSON.parse(e.target.result);
                            self.jsonFileName = file.name;
                            self.jsonError = '';
                            self.jsonSummary = self.buildJsonSummary(self.jsonData, file.size);
                        } catch (err) {
                            self.jsonError = '{{ __("JSON tidak valid") }}: ' + err.message;
                            self.jsonData = null;
                            self.jsonFileName = '';
                            self.jsonSummary = null;
                        }
                    };
                    reader.onerror = function() {
                        self.jsonError = '{{ __("Gagal membaca file") }}';
                    };
                    reader.readAsText(file);
                },

                removeJson() {
                    this.jsonData = null;
                    this.jsonFileName = '';
                    this.jsonError = '';
                    this.jsonSummary = null;
                    document.getElementById('json_upload').value = '';
                },

                buildJsonSummary(data, fileSize) {
                    var self = this;

                    function formatSize(bytes) {
                        if (bytes < 1024) return bytes + ' B';
                        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
                    }

                    function detectType(val) {
                        if (val === null) return 'null';
                        if (Array.isArray(val)) return 'array';
                        return typeof val;
                    }

                    function buildStructureLines(val, indent, maxDepth) {
                        var lines = [];
                        var prefix = '';
                        for (var i = 0; i < indent; i++) prefix += '  ';

                        if (maxDepth <= 0) {
                            lines.push({ indent: prefix, text: '...', isKey: false, valType: null });
                            return lines;
                        }

                        if (Array.isArray(val)) {
                            if (val.length === 0) {
                                lines.push({ indent: prefix, text: '[] (empty)', isKey: false, valType: null });
                            } else {
                                lines.push({ indent: prefix, text: 'Array[' + val.length + ']', isKey: false, valType: null });
                                var sample = val[0];
                                if (typeof sample === 'object' && sample !== null && !Array.isArray(sample)) {
                                    var sampleLines = buildStructureLines(sample, indent + 1, maxDepth - 1);
                                    for (var i = 0; i < sampleLines.length; i++) {
                                        lines.push(sampleLines[i]);
                                    }
                                } else {
                                    var childPrefix = '';
                                    for (var i = 0; i < indent + 1; i++) childPrefix += '  ';
                                    lines.push({ indent: childPrefix, text: '[' + detectType(sample) + ']', isKey: false, valType: detectType(sample) });
                                }
                            }
                        } else if (typeof val === 'object' && val !== null) {
                            var keys = Object.keys(val);
                            lines.push({ indent: prefix, text: '{' + keys.length + ' keys}', isKey: false, valType: null });
                            var childPrefix = '';
                            for (var i = 0; i < indent + 1; i++) childPrefix += '  ';
                            for (var k = 0; k < keys.length && k < 15; k++) {
                                var child = val[keys[k]];
                                var type = detectType(child);
                                if (type === 'object' && child !== null) {
                                    lines.push({ indent: childPrefix, text: keys[k] + ':', isKey: true, valType: null });
                                    var childLines = buildStructureLines(child, indent + 2, maxDepth - 1);
                                    for (var i = 0; i < childLines.length; i++) {
                                        lines.push(childLines[i]);
                                    }
                                } else if (type === 'array') {
                                    lines.push({ indent: childPrefix, text: keys[k] + ': Array[' + child.length + ']', isKey: true, valType: null });
                                } else {
                                    lines.push({ indent: childPrefix, text: keys[k] + ': ' + type, isKey: true, valType: type });
                                }
                            }
                            if (keys.length > 15) {
                                lines.push({ indent: childPrefix, text: '... +' + (keys.length - 15) + ' more', isKey: false, valType: null });
                            }
                        } else {
                            lines.push({ indent: prefix, text: String(val), isKey: false, valType: detectType(val) });
                        }

                        return lines;
                    }

                    function extractFields(data) {
                        var items = [];
                        if (Array.isArray(data)) {
                            items = data;
                        } else if (typeof data === 'object' && data !== null) {
                            var keys = Object.keys(data);
                            for (var i = 0; i < keys.length; i++) {
                                if (Array.isArray(data[keys[i]]) && data[keys[i]].length > 0 && typeof data[keys[i]][0] === 'object') {
                                    items = data[keys[i]];
                                    break;
                                }
                            }
                        }

                        if (items.length === 0) return [];

                        var fieldTypes = {};
                        var sampleSize = Math.min(items.length, 50);
                        for (var i = 0; i < sampleSize; i++) {
                            if (typeof items[i] !== 'object' || items[i] === null) continue;
                            var itemKeys = Object.keys(items[i]);
                            for (var j = 0; j < itemKeys.length; j++) {
                                var key = itemKeys[j];
                                var type = detectType(items[i][key]);
                                if (!fieldTypes[key]) {
                                    fieldTypes[key] = {};
                                }
                                fieldTypes[key][type] = true;
                            }
                        }

                        var fields = [];
                        var fieldNames = Object.keys(fieldTypes);
                        for (var i = 0; i < fieldNames.length; i++) {
                            var types = Object.keys(fieldTypes[fieldNames[i]]);
                            var nonNull = types.filter(function(t) { return t !== 'null'; });
                            var displayType = nonNull.length === 1 ? nonNull[0] : (nonNull.length > 1 ? 'mixed' : 'null');
                            fields.push({ name: fieldNames[i], type: displayType });
                        }
                        return fields;
                    }

                    var rootType = Array.isArray(data) ? 'Array' : 'Object';
                    var recordCount = null;

                    if (Array.isArray(data)) {
                        recordCount = data.length;
                    } else if (typeof data === 'object' && data !== null) {
                        var keys = Object.keys(data);
                        for (var i = 0; i < keys.length; i++) {
                            if (Array.isArray(data[keys[i]])) {
                                recordCount = data[keys[i]].length;
                                break;
                            }
                        }
                    }

                    return {
                        rootType: rootType,
                        recordCount: recordCount,
                        fileSize: formatSize(fileSize),
                        structureLines: buildStructureLines(data, 0, 4),
                        fields: extractFields(data),
                    };
                },

                updateLineNumbers() {
                    var lines = (this.code || '').split('\n');
                    this.lineCount = lines.length;
                    var nums = [];
                    for (var i = 1; i <= lines.length; i++) {
                        nums.push(i);
                    }
                    this.lineNumbers = nums.join('<br>');
                },

                syncScroll() {
                    var editor = this.$refs.editor;
                    var lineNums = this.$refs.lineNumbers;
                    if (lineNums) {
                        lineNums.scrollTop = editor.scrollTop;
                    }
                },

                handleTab(event) {
                    var editor = event.target;
                    var start = editor.selectionStart;
                    var end = editor.selectionEnd;

                    this.code = this.code.substring(0, start) + '  ' + this.code.substring(end);

                    this.$nextTick(function() {
                        editor.selectionStart = editor.selectionEnd = start + 2;
                    });
                },

                formatValue(value) {
                    if (value === undefined) return 'undefined';
                    if (value === null) return 'null';
                    if (typeof value === 'string') return value;
                    if (typeof value === 'function') return value.toString();
                    try {
                        return JSON.stringify(value, null, 2);
                    } catch (e) {
                        return String(value);
                    }
                },

                formatArgs(args) {
                    var self = this;
                    var parts = [];
                    for (var i = 0; i < args.length; i++) {
                        parts.push(self.formatValue(args[i]));
                    }
                    return parts.join(' ');
                },

                formatTable(data) {
                    if (!data || typeof data !== 'object') {
                        return this.formatValue(data);
                    }

                    var items = Array.isArray(data) ? data : [data];
                    if (items.length === 0) return '(empty)';

                    var keys = {};
                    for (var i = 0; i < items.length; i++) {
                        if (typeof items[i] === 'object' && items[i] !== null) {
                            var itemKeys = Object.keys(items[i]);
                            for (var j = 0; j < itemKeys.length; j++) {
                                keys[itemKeys[j]] = true;
                            }
                        }
                    }

                    var cols = Object.keys(keys);
                    if (cols.length === 0) return this.formatValue(data);

                    var widths = {};
                    for (var c = 0; c < cols.length; c++) {
                        widths[cols[c]] = cols[c].length;
                    }
                    var idxWidth = String(items.length - 1).length;
                    if (idxWidth < 5) idxWidth = 5;

                    for (var i = 0; i < items.length; i++) {
                        for (var c = 0; c < cols.length; c++) {
                            var val = items[i] && items[i][cols[c]] !== undefined ? String(items[i][cols[c]]) : '';
                            if (val.length > widths[cols[c]]) widths[cols[c]] = val.length;
                        }
                    }

                    for (var c = 0; c < cols.length; c++) {
                        if (widths[cols[c]] > 30) widths[cols[c]] = 30;
                    }

                    function pad(str, len) {
                        str = String(str);
                        if (str.length > len) str = str.substring(0, len - 1) + '…';
                        while (str.length < len) str += ' ';
                        return str;
                    }

                    var header = pad('Index', idxWidth) + ' │ ';
                    var sep = '';
                    for (var n = 0; n < idxWidth; n++) sep += '─';
                    sep += '─┼─';

                    for (var c = 0; c < cols.length; c++) {
                        header += pad(cols[c], widths[cols[c]]);
                        for (var n = 0; n < widths[cols[c]]; n++) sep += '─';
                        if (c < cols.length - 1) {
                            header += ' │ ';
                            sep += '─┼─';
                        }
                    }

                    var lines = [header, sep];
                    for (var i = 0; i < items.length; i++) {
                        var row = pad(i, idxWidth) + ' │ ';
                        for (var c = 0; c < cols.length; c++) {
                            var val = items[i] && items[i][cols[c]] !== undefined ? items[i][cols[c]] : '';
                            row += pad(val, widths[cols[c]]);
                            if (c < cols.length - 1) row += ' │ ';
                        }
                        lines.push(row);
                    }

                    return lines.join('\n');
                },

                run() {
                    if (!this.code.trim()) return;

                    this.output = [];
                    this.executionTime = null;

                    var self = this;
                    var captured = [];

                    var fakeConsole = {
                        log: function() { captured.push({ type: 'log', text: self.formatArgs(arguments) }); },
                        info: function() { captured.push({ type: 'info', text: self.formatArgs(arguments) }); },
                        warn: function() { captured.push({ type: 'warn', text: self.formatArgs(arguments) }); },
                        error: function() { captured.push({ type: 'error', text: self.formatArgs(arguments) }); },
                        table: function(data) { captured.push({ type: 'log', text: self.formatTable(data) }); },
                        clear: function() { self.output = []; captured = []; },
                    };

                    var start = performance.now();

                    try {
                        var fn = new Function('console', 'data', self.code);
                        var result = fn(fakeConsole, self.jsonData);
                        var elapsed = performance.now() - start;

                        for (var i = 0; i < captured.length; i++) {
                            self.output.push(captured[i]);
                        }

                        if (result !== undefined) {
                            self.output.push({ type: 'return', text: self.formatValue(result) });
                        }

                        self.executionTime = elapsed.toFixed(2);
                    } catch (err) {
                        var elapsed = performance.now() - start;

                        for (var i = 0; i < captured.length; i++) {
                            self.output.push(captured[i]);
                        }

                        self.output.push({ type: 'error', text: err.name + ': ' + err.message });
                        self.executionTime = elapsed.toFixed(2);
                    }

                    this.$nextTick(function() {
                        var outputEl = self.$refs.output;
                        if (outputEl) {
                            outputEl.scrollTop = outputEl.scrollHeight;
                        }
                    });
                },

                copyOutput() {
                    var text = this.output
                        .filter(function(e) { return e.type !== 'separator'; })
                        .map(function(e) { return e.text; })
                        .join('\n');

                    var self = this;
                    navigator.clipboard.writeText(text).then(function() {
                        self.copied = true;
                        setTimeout(function() { self.copied = false; }, 2000);
                    });
                },

                clearOutput() {
                    this.output = [];
                    this.executionTime = null;
                    this.copied = false;
                },

                clearCode() {
                    this.code = '';
                    this.updateLineNumbers();
                },

                loadExample() {
                    if (this.jsonData) {
                        this.code = '// Data JSON tersedia di variabel `data`\nconsole.log("Tipe data:", typeof data);\nconsole.log("Isi data:", data);\n\n// Contoh manipulasi (jika array)\nif (Array.isArray(data)) {\n  console.log("Jumlah item:", data.length);\n  console.table(data.slice(0, 5));\n\n  // Filter contoh\n  // const filtered = data.filter(item => item.name);\n  // console.table(filtered);\n}';
                    } else {
                        this.code = '// Contoh dasar JavaScript\nconst items = [\n  { name: "Alice", age: 30, city: "Jakarta" },\n  { name: "Bob", age: 25, city: "Bandung" },\n  { name: "Charlie", age: 35, city: "Surabaya" },\n];\n\nconsole.log("Semua item:");\nconsole.table(items);\n\n// Filter\nconst filtered = items.filter(i => i.age >= 30);\nconsole.log("\\nUmur >= 30:");\nconsole.table(filtered);\n\n// Map\nconst names = items.map(i => i.name);\nconsole.log("\\nNama:", names);\n\n// Reduce\nconst totalAge = items.reduce((sum, i) => sum + i.age, 0);\nconsole.log("Total umur:", totalAge);\nconsole.log("Rata-rata:", totalAge / items.length);';
                    }
                    this.updateLineNumbers();
                },
            };
        }
    </script>
@endsection

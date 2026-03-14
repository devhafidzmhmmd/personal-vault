<?php

namespace App\Http\Controllers;

use App\Exports\JsonToExcelExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ToolController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('tools.json-to-excel');
    }

    public function jsonToExcel(): View
    {
        return view('tools.json-to-excel');
    }

    public function jsPlayground(): View
    {
        return view('tools.js-playground');
    }

    public function convertJsonToExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $request->validate([
            'json_file' => ['required', 'file', 'max:10240'],
            'json_path' => ['nullable', 'string'],
            'document_name' => ['nullable', 'string', 'max:255'],
        ]);

        $extension = strtolower($request->file('json_file')->getClientOriginalExtension());
        if ($extension !== 'json') {
            return back()->withErrors(['json_file' => __('File harus berformat .json.')]);
        }

        $content = file_get_contents($request->file('json_file')->getRealPath());
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['json_file' => __('File JSON tidak valid: :error', ['error' => json_last_error_msg()])]);
        }

        if (! is_array($decoded)) {
            return back()->withErrors(['json_file' => __('File JSON harus berisi array of objects.')]);
        }

        $jsonPath = $request->input('json_path');
        $items = $this->resolveItems($decoded, $jsonPath);

        if ($items === null) {
            return back()->withErrors(['json_file' => __('Key ":key" tidak ditemukan atau bukan array.', ['key' => $jsonPath])]);
        }

        $headings = $this->extractHeadings(collect($items));

        if (empty($headings)) {
            return back()->withErrors(['json_file' => __('Tidak ada data yang dapat dikonversi.')]);
        }

        $data = collect($items);
        $documentName = $request->input('document_name');
        $filename = (! empty($documentName) ? $documentName : pathinfo($request->file('json_file')->getClientOriginalName(), PATHINFO_FILENAME)).'.xlsx';

        return Excel::download(new JsonToExcelExport($data, $headings), $filename);
    }

    /**
     * @return array<int, mixed>|null
     */
    private function resolveItems(array $decoded, ?string $jsonPath): ?array
    {
        if (! $jsonPath || $jsonPath === '') {
            if (isset($decoded[0]) && is_array($decoded[0])) {
                return $decoded;
            }

            return [$decoded];
        }

        if (! array_key_exists($jsonPath, $decoded) || ! is_array($decoded[$jsonPath])) {
            return null;
        }

        return $decoded[$jsonPath];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<int, string>
     */
    private function extractHeadings(Collection $items): array
    {
        $keys = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                foreach (array_keys($item) as $key) {
                    $keys[$key] = true;
                }
            }
        }

        return array_keys($keys);
    }
}

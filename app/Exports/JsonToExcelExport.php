<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JsonToExcelExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(
        private Collection $data,
        private array $headings,
    ) {}

    public function collection(): Collection
    {
        return $this->data->map(function (array $row): array {
            $mapped = [];
            foreach ($this->headings as $key) {
                $value = $row[$key] ?? '';
                $mapped[] = is_array($value) || is_object($value)
                    ? json_encode($value, JSON_UNESCAPED_UNICODE)
                    : $value;
            }

            return $mapped;
        });
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class StudentImportTemplate implements FromArray, WithStyles, WithTitle
{
    public function array(): array
    {
        return [
            ['Full Name', 'Date of Birth', 'Gender', 'Class Name', 'Section Name', 'Guardian Name', 'Guardian Contact', 'Guardian Email', 'Medical Notes'],
            ['John Doe', '2012-05-15', 'Male', 'Primary 4', 'A', 'Jane Doe', '+233 20 123 4567', 'parent@example.com', ''],
        ];
    }

    public function title(): string
    {
        return 'Students';
    }

    public function styles(Worksheet $sheet): void
    {
        // Header row: bold + blue background + white text
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']],
        ]);

        // Example row: grey text to signal it is sample data
        $sheet->getStyle('A2:I2')->applyFromArray([
            'font' => ['color' => ['argb' => 'FF9CA3AF'], 'italic' => true],
        ]);

        // Column widths for readability
        foreach (['A' => 28, 'B' => 18, 'C' => 10, 'D' => 22, 'E' => 16, 'F' => 28, 'G' => 22, 'H' => 32, 'I' => 28] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }
}

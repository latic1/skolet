<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class StaffImportTemplate implements FromArray, WithStyles, WithTitle
{
    public function array(): array
    {
        return [
            ['Full Name', 'Email', 'Phone', 'Role', 'Role Title'],
            ['Kofi Mensah', 'kofi.mensah@school.edu.gh', '+233 24 000 0000', 'teacher', 'Class Teacher'],
        ];
    }

    public function title(): string
    {
        return 'Staff';
    }

    public function styles(Worksheet $sheet): void
    {
        // Header row: bold + blue background + white text
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']],
        ]);

        // Example row: grey italic to signal it is sample data
        $sheet->getStyle('A2:E2')->applyFromArray([
            'font' => ['color' => ['argb' => 'FF9CA3AF'], 'italic' => true],
        ]);

        // Column widths for readability
        foreach (['A' => 28, 'B' => 35, 'C' => 22, 'D' => 20, 'E' => 28] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }
}

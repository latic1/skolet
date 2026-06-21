<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

final class AttendanceReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly array $data) {}

    public function collection(): Collection
    {
        return collect($this->data['rows'])->map(fn (array $row): array => [
            $row['student']->full_name,
            $row['student']->admission_no,
            $row['present'],
            $row['absent'],
            $row['late'],
            $row['total_marked'],
            number_format($row['percent_present'], 1) . '%',
        ]);
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Adm. No.',
            'Present',
            'Absent',
            'Late',
            'Days Marked',
            '% Present',
        ];
    }

    public function title(): string
    {
        $class   = $this->data['class']->name;
        $section = $this->data['section']?->name;

        return $section ? "{$class} – {$section}" : $class;
    }
}

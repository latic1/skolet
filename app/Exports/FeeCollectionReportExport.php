<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

final class FeeCollectionReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly array $data) {}

    public function collection(): Collection
    {
        $rows = collect($this->data['rows'])->map(fn (array $row): array => [
            $row['class']->name,
            $row['fee_structure']->fee_item,
            number_format((float) $row['fee_structure']->amount, 2),
            $row['student_count'],
            number_format($row['expected'], 2),
            number_format($row['collected'], 2),
            number_format($row['outstanding'], 2),
        ]);

        // Append totals row
        $rows->push([
            'TOTAL',
            '',
            '',
            '',
            number_format($this->data['total_expected'], 2),
            number_format($this->data['total_collected'], 2),
            number_format($this->data['total_outstanding'], 2),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Class',
            'Fee Item',
            'Amount / Student',
            '# Students',
            'Total Expected',
            'Total Collected',
            'Outstanding',
        ];
    }

    public function title(): string
    {
        return $this->data['term']->name;
    }
}

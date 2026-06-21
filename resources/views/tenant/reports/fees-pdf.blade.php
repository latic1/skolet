<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Fee Collection Report</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #101828; background: #ffffff; }

    .page { padding: 28px 32px; }

    /* Header */
    .school-header { display: table; width: 100%; border-bottom: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 16px; }
    .school-header-left { display: table-cell; vertical-align: middle; }
    .school-header-right { display: table-cell; text-align: right; vertical-align: middle; }
    .school-logo { width: 40px; height: 40px; object-fit: contain; }
    .school-name { font-size: 14px; font-weight: bold; color: #101828; margin-bottom: 2px; }
    .report-title { font-size: 12px; font-weight: bold; color: #2563eb; text-transform: uppercase; letter-spacing: 0.04em; }

    /* Meta block */
    .meta-block { background: #f9fafb; border: 1px solid #e7eaf3; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; display: table; width: 100%; }
    .meta-item { display: table-cell; padding-right: 24px; }
    .meta-label { font-size: 8px; font-weight: bold; color: #6a7282; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px; }
    .meta-value { font-size: 10px; font-weight: 600; color: #101828; }

    /* Summary row */
    .summary-row { display: table; width: 100%; margin-bottom: 16px; border: 1px solid #e7eaf3; border-radius: 6px; overflow: hidden; }
    .summary-cell { display: table-cell; padding: 10px 16px; width: 33.33%; border-right: 1px solid #e7eaf3; }
    .summary-cell:last-child { border-right: none; }
    .summary-label { font-size: 8px; font-weight: bold; color: #6a7282; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
    .summary-value { font-size: 13px; font-weight: bold; }

    /* Table */
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #2563eb; color: #ffffff; }
    thead th { padding: 7px 9px; font-size: 8px; font-weight: bold; text-align: left; text-transform: uppercase; letter-spacing: 0.06em; }
    thead th.right { text-align: right; }
    thead th.center { text-align: center; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    tbody tr:nth-child(odd)  { background: #ffffff; }
    tbody td { padding: 6px 9px; font-size: 10px; color: #101828; border-bottom: 1px solid #e7eaf3; }
    tbody td.right { text-align: right; }
    tbody td.center { text-align: center; }
    tbody td.cleared { color: #059669; font-weight: 600; }
    tbody td.owed    { color: #dc2626; font-weight: 600; }
    tfoot tr { background: #eef2ff; }
    tfoot td { padding: 8px 9px; font-size: 10px; font-weight: bold; color: #101828; border-top: 2px solid #2563eb; }
    tfoot td.right { text-align: right; }

    /* Footer */
    .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #e7eaf3; font-size: 8px; color: #99a1af; text-align: center; }
</style>
</head>
<body>
<div class="page">

    {{-- School header --}}
    <div class="school-header">
        <div class="school-header-left">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="school-logo" style="margin-bottom:4px">
            @endif
            <div class="school-name">{{ $profile?->school_name ?? tenant('name') ?? 'School' }}</div>
        </div>
        <div class="school-header-right">
            <div class="report-title">Fee Collection Report</div>
        </div>
    </div>

    {{-- Meta --}}
    <div class="meta-block">
        <div class="meta-item">
            <div class="meta-label">Term</div>
            <div class="meta-value">{{ $report['term']->name }}</div>
        </div>
        @if($report['term']->academicYear)
        <div class="meta-item">
            <div class="meta-label">Academic Year</div>
            <div class="meta-value">{{ $report['term']->academicYear->name }}</div>
        </div>
        @endif
        <div class="meta-item">
            <div class="meta-label">Fee Structures</div>
            <div class="meta-value">{{ count($report['rows']) }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Generated</div>
            <div class="meta-value">{{ now()->format('d M Y') }}</div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary-row">
        <div class="summary-cell">
            <div class="summary-label">Total Expected</div>
            <div class="summary-value" style="color:#101828">{{ number_format($report['total_expected'], 2) }}</div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Total Collected</div>
            <div class="summary-value" style="color:#059669">{{ number_format($report['total_collected'], 2) }}</div>
        </div>
        <div class="summary-cell">
            <div class="summary-label">Outstanding</div>
            <div class="summary-value" style="color:{{ $report['total_outstanding'] > 0 ? '#dc2626' : '#059669' }}">
                {{ number_format($report['total_outstanding'], 2) }}
            </div>
        </div>
    </div>

    {{-- Table --}}
    @if(count($report['rows']) === 0)
        <p style="text-align:center; color:#99a1af; margin-top:20px">No fee structures defined for this term.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Fee Item</th>
                    <th class="right">Amount / Student</th>
                    <th class="center">Students</th>
                    <th class="right">Total Expected</th>
                    <th class="right">Total Collected</th>
                    <th class="right">Outstanding</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['rows'] as $row)
                    <tr>
                        <td style="font-weight:600">{{ $row['class']->name }}</td>
                        <td>{{ $row['fee_structure']->fee_item }}</td>
                        <td class="right" style="color:#6a7282">{{ number_format((float)$row['fee_structure']->amount, 2) }}</td>
                        <td class="center" style="color:#6a7282">{{ $row['student_count'] }}</td>
                        <td class="right">{{ number_format($row['expected'], 2) }}</td>
                        <td class="right" style="{{ $row['collected'] > 0 ? 'color:#059669;font-weight:600' : 'color:#99a1af' }}">
                            {{ number_format($row['collected'], 2) }}
                        </td>
                        <td class="right {{ $row['outstanding'] > 0 ? 'owed' : 'cleared' }}">
                            {{ $row['outstanding'] > 0 ? number_format($row['outstanding'], 2) : 'Cleared' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="font-weight:bold">TOTAL</td>
                    <td class="right">{{ number_format($report['total_expected'], 2) }}</td>
                    <td class="right" style="color:#059669">{{ number_format($report['total_collected'], 2) }}</td>
                    <td class="right" style="color:{{ $report['total_outstanding'] > 0 ? '#dc2626' : '#059669' }}">
                        {{ number_format($report['total_outstanding'], 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="footer">Generated by SchoolFlow · {{ now()->format('d M Y, H:i') }}</div>
</div>
</body>
</html>

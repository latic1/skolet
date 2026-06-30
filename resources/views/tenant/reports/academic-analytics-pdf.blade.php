<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Academic Analytics</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #101828; background: #ffffff; }

    .page { padding: 32px 36px; }

    .school-header { display: table; width: 100%; border-bottom: 2px solid #2563eb; padding-bottom: 14px; margin-bottom: 18px; }
    .school-header-left { display: table-cell; vertical-align: middle; }
    .school-header-right { display: table-cell; text-align: right; vertical-align: middle; }
    .school-logo { width: 44px; height: 44px; object-fit: contain; }
    .school-name { font-size: 15px; font-weight: bold; color: #101828; margin-bottom: 2px; }
    .report-title { font-size: 13px; font-weight: bold; color: #2563eb; text-transform: uppercase; letter-spacing: 0.04em; }

    .meta-block { background: #f9fafb; border: 1px solid #e7eaf3; border-radius: 6px; padding: 10px 14px; margin-bottom: 18px; display: table; width: 100%; }
    .meta-item { display: table-cell; padding-right: 24px; }
    .meta-label { font-size: 9px; font-weight: bold; color: #6a7282; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px; }
    .meta-value { font-size: 11px; font-weight: 600; color: #101828; }

    table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    thead tr { background: #2563eb; color: #ffffff; }
    thead th { padding: 8px 10px; font-size: 9px; font-weight: bold; text-align: left; text-transform: uppercase; letter-spacing: 0.06em; }
    thead th.center { text-align: center; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    tbody tr:nth-child(odd)  { background: #ffffff; }
    tbody td { padding: 7px 10px; font-size: 10px; color: #101828; border-bottom: 1px solid #e7eaf3; }
    tbody td.center { text-align: center; }
    tfoot tr { background: #eef2ff; }
    tfoot td { padding: 8px 10px; font-size: 10px; font-weight: bold; color: #101828; border-top: 2px solid #2563eb; }

    .pass { color: #16a34a; font-weight: bold; }
    .fail { color: #dc2626; font-weight: bold; }

    .footer { margin-top: 24px; border-top: 1px solid #e7eaf3; padding-top: 10px; display: table; width: 100%; }
    .footer-left { display: table-cell; font-size: 9px; color: #6a7282; }
    .footer-right { display: table-cell; text-align: right; font-size: 9px; color: #6a7282; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="school-header">
        <div class="school-header-left">
            @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="school-logo" alt="Logo" style="margin-bottom:4px">
            @endif
            <div class="school-name">{{ $profile?->school_name ?? 'School' }}</div>
        </div>
        <div class="school-header-right">
            <div class="report-title">Academic Analytics Report</div>
            <div style="font-size:10px;color:#6a7282;margin-top:4px">Generated {{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    {{-- Meta --}}
    <div class="meta-block">
        <div class="meta-item">
            <div class="meta-label">Exam</div>
            <div class="meta-value">{{ $report['exam']?->name ?? '—' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Class</div>
            <div class="meta-value">{{ $report['class']?->name ?? '—' }}{{ $report['section'] ? ' / ' . $report['section']->name : '' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Term</div>
            <div class="meta-value">{{ $report['exam']?->term?->name ?? '—' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Pass Threshold</div>
            <div class="meta-value">{{ $report['pass_threshold'] }}%</div>
        </div>
    </div>

    @if(count($report['subjects']) === 0)
    <p style="color:#6a7282;font-size:11px;text-align:center;padding:32px 0">No exam results found for the selected filters.</p>
    @else

    {{-- Summary stats strip --}}
    @php
        $subjectCount  = count($report['subjects']);
        $overallAvg    = $subjectCount > 0 ? round(collect($report['subjects'])->avg('avg_score'), 1) : 0;
        $overallPass   = $subjectCount > 0 ? round(collect($report['subjects'])->avg('pass_rate'), 1) : 0;
        $totalStudents = collect($report['subjects'])->max('student_count') ?? 0;
    @endphp
    <table style="margin-bottom:18px">
        <thead>
            <tr>
                <th>Subjects Assessed</th>
                <th class="center">Total Students</th>
                <th class="center">Overall Avg Score</th>
                <th class="center">Overall Pass Rate</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $subjectCount }}</td>
                <td class="center">{{ $totalStudents }}</td>
                <td class="center">{{ $overallAvg }}</td>
                <td class="center {{ $overallPass >= 50 ? 'pass' : 'fail' }}">{{ $overallPass }}%</td>
            </tr>
        </tbody>
    </table>

    {{-- Per-subject table --}}
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th class="center">Students</th>
                <th class="center">Avg Score</th>
                <th class="center">Highest</th>
                <th class="center">Lowest</th>
                <th class="center">Pass Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['subjects'] as $row)
            @php $passOk = $row['pass_rate'] >= 50; @endphp
            <tr>
                <td>{{ $row['subject_name'] }}</td>
                <td class="center">{{ $row['student_count'] }}</td>
                <td class="center" style="font-weight:600">{{ $row['avg_score'] }}</td>
                <td class="center">{{ $row['highest'] }}</td>
                <td class="center">{{ $row['lowest'] }}</td>
                <td class="center {{ $passOk ? 'pass' : 'fail' }}">{{ $row['pass_rate'] }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Averages</td>
                <td class="center">{{ $overallAvg }}</td>
                <td class="center">&mdash;</td>
                <td class="center">&mdash;</td>
                <td class="center">{{ $overallPass }}%</td>
            </tr>
        </tfoot>
    </table>

    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">{{ $profile?->school_name }}</div>
        <div class="footer-right">Academic Analytics &bull; Confidential</div>
    </div>

</div>
</body>
</html>

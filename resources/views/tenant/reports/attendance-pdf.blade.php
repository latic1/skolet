<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Attendance Report</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #101828; background: #ffffff; }

    .page { padding: 32px 36px; }

    /* Header */
    .school-header { display: table; width: 100%; border-bottom: 2px solid #2563eb; padding-bottom: 14px; margin-bottom: 18px; }
    .school-header-left { display: table-cell; vertical-align: middle; }
    .school-header-right { display: table-cell; text-align: right; vertical-align: middle; }
    .school-logo { width: 44px; height: 44px; object-fit: contain; }
    .school-name { font-size: 15px; font-weight: bold; color: #101828; margin-bottom: 2px; }
    .report-title { font-size: 13px; font-weight: bold; color: #2563eb; text-transform: uppercase; letter-spacing: 0.04em; }

    /* Meta block */
    .meta-block { background: #f9fafb; border: 1px solid #e7eaf3; border-radius: 6px; padding: 10px 14px; margin-bottom: 18px; display: table; width: 100%; }
    .meta-item { display: table-cell; padding-right: 24px; }
    .meta-label { font-size: 9px; font-weight: bold; color: #6a7282; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px; }
    .meta-value { font-size: 11px; font-weight: 600; color: #101828; }

    /* Table */
    table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    thead tr { background: #2563eb; color: #ffffff; }
    thead th { padding: 8px 10px; font-size: 9px; font-weight: bold; text-align: left; text-transform: uppercase; letter-spacing: 0.06em; }
    thead th.center { text-align: center; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    tbody tr:nth-child(odd)  { background: #ffffff; }
    tbody td { padding: 7px 10px; font-size: 10px; color: #101828; border-bottom: 1px solid #e7eaf3; }
    tbody td.center { text-align: center; }
    tbody td.num { text-align: right; }
    tfoot tr { background: #eef2ff; }
    tfoot td { padding: 8px 10px; font-size: 10px; font-weight: bold; color: #101828; border-top: 2px solid #2563eb; }

    /* Progress bar */
    .bar-track { width: 60px; height: 5px; background: #e7eaf3; border-radius: 9999px; display: inline-block; vertical-align: middle; }
    .bar-fill  { height: 5px; border-radius: 9999px; display: block; }

    /* Badge */
    .badge { display: inline-block; padding: 2px 7px; border-radius: 9999px; font-size: 9px; font-weight: 600; }
    .badge-present { background: #d1fae5; color: #065f46; }
    .badge-absent  { background: #fee2e2; color: #b91c1c; }
    .badge-late    { background: #fff7ed; color: #c2410c; }

    /* Footer */
    .footer { margin-top: 28px; padding-top: 10px; border-top: 1px solid #e7eaf3; font-size: 9px; color: #99a1af; text-align: center; }
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
            <div class="report-title">Attendance Report</div>
        </div>
    </div>

    {{-- Meta --}}
    <div class="meta-block">
        <div class="meta-item">
            <div class="meta-label">Class</div>
            <div class="meta-value">
                {{ $report['class']->name }}
                @if($report['section'])
                    &mdash; {{ $report['section']->name }}
                @endif
            </div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Period</div>
            <div class="meta-value">{{ $report['date_from']->format('d M Y') }} &mdash; {{ $report['date_to']->format('d M Y') }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Students</div>
            <div class="meta-value">{{ count($report['rows']) }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Generated</div>
            <div class="meta-value">{{ now()->format('d M Y') }}</div>
        </div>
    </div>

    {{-- Table --}}
    @if(count($report['rows']) === 0)
        <p style="text-align:center; color:#99a1af; margin-top:24px">No students found for this class/section.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:28px">#</th>
                    <th>Student Name</th>
                    <th>Adm. No.</th>
                    <th class="center">Present</th>
                    <th class="center">Absent</th>
                    <th class="center">Late</th>
                    <th class="center">Days Marked</th>
                    <th class="center">% Present</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['rows'] as $i => $row)
                    @php
                        $pct      = $row['percent_present'];
                        $barColor = $pct >= 80 ? '#10B981' : ($pct >= 60 ? '#FF8904' : '#EF4444');
                        $barWidth = min(100, round($pct));
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight:500">{{ $row['student']->full_name }}</td>
                        <td style="color:#6a7282">{{ $row['student']->admission_no }}</td>
                        <td class="center">
                            <span class="badge badge-present">{{ $row['present'] }}</span>
                        </td>
                        <td class="center">
                            @if($row['absent'] > 0)
                                <span class="badge badge-absent">{{ $row['absent'] }}</span>
                            @else
                                <span style="color:#99a1af">0</span>
                            @endif
                        </td>
                        <td class="center">
                            @if($row['late'] > 0)
                                <span class="badge badge-late">{{ $row['late'] }}</span>
                            @else
                                <span style="color:#99a1af">0</span>
                            @endif
                        </td>
                        <td class="center" style="color:#6a7282">{{ $row['total_marked'] }}</td>
                        <td class="center">
                            <div style="display:inline-block; vertical-align:middle; margin-right:5px">
                                <div class="bar-track">
                                    <div class="bar-fill" style="width:{{ $barWidth }}%; background-color:{{ $barColor }}"></div>
                                </div>
                            </div>
                            <span style="font-weight:600; color:{{ $barColor }}">{{ number_format($pct, 1) }}%</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">Generated by Skolet &middot; {{ now()->format('d M Y, H:i') }}</div>
</div>
</body>
</html>

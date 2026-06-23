<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report Card — {{ $student->full_name }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #101828;
        background: #ffffff;
        padding: 32px;
    }

    /* ── Header ── */
    .header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding-bottom: 20px;
        border-bottom: 2px solid #2563eb;
        margin-bottom: 20px;
    }
    .school-name {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 2px;
    }
    .school-sub {
        font-size: 11px;
        color: #6a7282;
    }
    .report-title {
        text-align: right;
    }
    .report-title h1 {
        font-size: 14px;
        font-weight: 700;
        color: #2563eb;
        margin-bottom: 2px;
    }
    .report-title p {
        font-size: 10px;
        color: #6a7282;
    }

    /* ── Student info grid ── */
    .info-grid {
        display: table;
        width: 100%;
        border: 1px solid #e7eaf3;
        border-radius: 8px;
        margin-bottom: 20px;
        overflow: hidden;
    }
    .info-row {
        display: table-row;
        background: #f9fafb;
    }
    .info-row:nth-child(odd) { background: #ffffff; }
    .info-cell {
        display: table-cell;
        padding: 8px 14px;
        font-size: 10px;
        width: 50%;
        vertical-align: top;
    }
    .info-label {
        color: #99a1af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 9px;
        display: block;
        margin-bottom: 2px;
    }
    .info-value {
        font-weight: 600;
        color: #101828;
    }

    /* ── Results table ── */
    table.results {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
    }
    table.results thead tr {
        background: #2563eb;
        color: #ffffff;
    }
    table.results thead th {
        padding: 8px 12px;
        font-size: 10px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    table.results thead th.center { text-align: center; }
    table.results tbody tr {
        border-bottom: 1px solid #e7eaf3;
    }
    table.results tbody tr:last-child { border-bottom: none; }
    table.results tbody tr:nth-child(even) { background: #f9fafb; }
    table.results tbody td {
        padding: 8px 12px;
        font-size: 11px;
        color: #101828;
    }
    table.results tbody td.center { text-align: center; }
    table.results tfoot tr {
        background: #eff6ff;
        border-top: 2px solid #2563eb;
    }
    table.results tfoot td {
        padding: 10px 12px;
        font-size: 11px;
        font-weight: 700;
        color: #101828;
    }
    table.results tfoot td.center { text-align: center; }

    /* ── Grade badge ── */
    .grade-badge {
        display: inline-block;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 700;
        text-align: center;
        line-height: 22px;
    }
    .grade-A { background: #ecfdf5; color: #007a55; }
    .grade-B { background: #ecfeff; color: #0891b2; }
    .grade-C { background: #fff7ed; color: #ff8904; }
    .grade-D, .grade-F { background: #fef2f2; color: #ef4444; }

    /* ── Progress bar ── */
    .bar-track {
        width: 80px;
        height: 5px;
        background: #e7eaf3;
        border-radius: 9999px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
    }
    .bar-fill {
        height: 5px;
        border-radius: 9999px;
    }

    /* ── Grading scale key ── */
    .scale-section {
        margin-top: 16px;
        border: 1px solid #e7eaf3;
        border-radius: 8px;
        overflow: hidden;
    }
    .scale-header {
        background: #f9fafb;
        padding: 8px 14px;
        font-size: 10px;
        font-weight: 600;
        color: #6a7282;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .scale-body {
        display: table;
        width: 100%;
    }
    .scale-row {
        display: table-row;
    }
    .scale-cell {
        display: table-cell;
        padding: 6px 14px;
        font-size: 10px;
        color: #6a7282;
        border-right: 1px solid #e7eaf3;
        text-align: center;
    }
    .scale-cell:last-child { border-right: none; }

    /* ── Footer ── */
    .footer {
        margin-top: 28px;
        border-top: 1px solid #e7eaf3;
        padding-top: 14px;
        display: table;
        width: 100%;
    }
    .footer-col {
        display: table-cell;
        width: 33%;
        vertical-align: top;
    }
    .footer-label {
        font-size: 9px;
        color: #99a1af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-top: 1px solid #6a7282;
        padding-top: 4px;
        margin-top: 24px;
    }
    .watermark {
        text-align: center;
        font-size: 9px;
        color: #99a1af;
        margin-top: 16px;
    }
</style>
</head>
<body>

{{-- ── Header ── --}}
<div class="header">
    <div>
        @if($logoBase64)
        <img src="{{ $logoBase64 }}" alt="School Logo"
             style="width: 44px; height: 44px; border-radius: 6px; object-fit: contain; display: block; margin-bottom: 6px;">
        @endif
        <div class="school-name">{{ $schoolProfile?->school_name ?? tenant('name') ?? 'School' }}</div>
        <div class="school-sub">Report Card</div>
    </div>
    <div class="report-title">
        <h1>REPORT CARD</h1>
        <p>{{ $exam->name }}
        @if($exam->term) · {{ $exam->term->name }}@endif
        @if($exam->term?->academicYear) · {{ $exam->term->academicYear->name }}@endif
        </p>
        @if($exam->start_date && $exam->end_date)
        <p>{{ $exam->start_date->format('d M Y') }} – {{ $exam->end_date->format('d M Y') }}</p>
        @endif
    </div>
</div>

{{-- ── Student Info ── --}}
<div class="info-grid">
    <div class="info-row">
        <div class="info-cell">
            <span class="info-label">Student Name</span>
            <span class="info-value">{{ $student->full_name }}</span>
        </div>
        <div class="info-cell">
            <span class="info-label">Admission No.</span>
            <span class="info-value">{{ $student->admission_no }}</span>
        </div>
    </div>
    <div class="info-row">
        <div class="info-cell">
            <span class="info-label">Class</span>
            <span class="info-value">
                {{ $student->schoolClass?->name ?? '—' }}
                @if($student->section) / {{ $student->section->name }}@endif
            </span>
        </div>
        <div class="info-cell">
            <span class="info-label">Date Issued</span>
            <span class="info-value">{{ now()->format('d M Y') }}</span>
        </div>
    </div>
</div>

{{-- ── Results Table ── --}}
@if($results->isNotEmpty())
<table class="results">
    <thead>
        <tr>
            <th style="width:28px">#</th>
            <th>Subject</th>
            <th class="center" style="width:80px">Marks (/100)</th>
            <th class="center" style="width:60px">Grade</th>
            <th style="width:90px">Remark</th>
            <th style="width:90px">Progress</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row['subject'] }}</td>
            <td class="center"><strong>{{ number_format($row['marks'], 1) }}</strong></td>
            <td class="center">
                <span class="grade-badge grade-{{ $row['grade'] }}">{{ $row['grade'] }}</span>
            </td>
            <td>{{ $row['remark'] }}</td>
            <td>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ $row['bar_width'] }}%; background-color:{{ $row['bar_color'] }}"></div>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
    @if($average !== null)
    <tfoot>
        <tr>
            <td colspan="2"><strong>Overall Average</strong></td>
            <td class="center"><strong>{{ number_format($average, 1) }}</strong></td>
            <td class="center">
                @if($average_grade)
                <span class="grade-badge grade-{{ $average_grade }}">{{ $average_grade }}</span>
                @endif
            </td>
            <td><strong>{{ $average_remark ?? '' }}</strong></td>
            <td>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ min(100, (int) round($average)) }}%; background-color:{{ $results->first()['bar_color'] }}"></div>
                </div>
            </td>
        </tr>
    </tfoot>
    @endif
</table>

{{-- ── Grading Scale ── --}}
<div class="scale-section">
    <div class="scale-header">Grading Scale</div>
    <div class="scale-body">
        <div class="scale-row">
            @foreach($scale as $band)
            <div class="scale-cell">
                <strong>{{ $band['grade'] }}</strong> — {{ $band['min'] }}–{{ $band['max'] }} ({{ $band['remark'] }})
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Signature area ── --}}
<div class="footer">
    <div class="footer-col">
        <div class="footer-label">Class Teacher</div>
    </div>
    <div class="footer-col" style="text-align:center">
        <div class="footer-label">Head Teacher / Principal</div>
    </div>
    <div class="footer-col" style="text-align:right">
        <div class="footer-label">School Stamp</div>
    </div>
</div>

@else
<p style="color:#99a1af; font-size:12px; text-align:center; padding:40px 0">
    No marks have been recorded for this student in this exam.
</p>
@endif

<div class="watermark">
    Generated by Skolet · {{ now()->format('d M Y, H:i') }}
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Transcript &mdash; {{ $student->full_name }}</title>
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
        display: table;
        width: 100%;
        padding-bottom: 20px;
        border-bottom: 2px solid #2563eb;
        margin-bottom: 20px;
    }
    .header-left  { display: table-cell; vertical-align: top; }
    .header-right { display: table-cell; vertical-align: top; text-align: right; }
    .school-name {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 2px;
    }
    .school-sub { font-size: 11px; color: #6a7282; }
    .report-title h1 {
        font-size: 14px;
        font-weight: 700;
        color: #2563eb;
        margin-bottom: 2px;
    }
    .report-title p { font-size: 10px; color: #6a7282; }

    /* ── Student info grid ── */
    .info-grid {
        display: table;
        width: 100%;
        border: 1px solid #e7eaf3;
        border-radius: 8px;
        margin-bottom: 24px;
        overflow: hidden;
    }
    .info-row { display: table-row; background: #f9fafb; }
    .info-row:nth-child(odd) { background: #ffffff; }
    .info-cell {
        display: table-cell;
        padding: 8px 14px;
        font-size: 10px;
        width: 33%;
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
    .info-value { font-weight: 600; color: #101828; }

    /* ── Year section ── */
    .year-header {
        background: #2563eb;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        padding: 8px 14px;
        border-radius: 6px 6px 0 0;
        margin-top: 20px;
        display: table;
        width: 100%;
    }
    .year-header-left  { display: table-cell; }
    .year-header-right { display: table-cell; text-align: right; font-size: 10px; font-weight: 400; }

    /* ── Term section ── */
    .term-header {
        background: #eff6ff;
        border-left: 3px solid #2563eb;
        padding: 6px 12px;
        font-size: 10px;
        font-weight: 700;
        color: #1d4ed8;
        margin-top: 0;
        display: table;
        width: 100%;
    }
    .term-header-left  { display: table-cell; }
    .term-header-right { display: table-cell; text-align: right; font-size: 10px; font-weight: 400; color: #6a7282; }

    /* ── Exam label ── */
    .exam-label {
        font-size: 10px;
        font-weight: 600;
        color: #374151;
        padding: 5px 12px 3px 12px;
        background: #f9fafb;
        border-bottom: 1px solid #e7eaf3;
    }

    /* ── Results table ── */
    table.results {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    table.results thead tr { background: #374151; color: #ffffff; }
    table.results thead th {
        padding: 6px 12px;
        font-size: 9px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    table.results thead th.center { text-align: center; }
    table.results tbody tr { border-bottom: 1px solid #e7eaf3; }
    table.results tbody tr:last-child { border-bottom: none; }
    table.results tbody tr:nth-child(even) { background: #f9fafb; }
    table.results tbody td {
        padding: 6px 12px;
        font-size: 10px;
        color: #101828;
    }
    table.results tbody td.center { text-align: center; }
    table.results tfoot tr { background: #f3f4f6; border-top: 1px solid #d1d5db; }
    table.results tfoot td {
        padding: 6px 12px;
        font-size: 10px;
        font-weight: 700;
        color: #101828;
    }
    table.results tfoot td.center { text-align: center; }

    /* ── Year total row ── */
    .year-total {
        display: table;
        width: 100%;
        background: #dbeafe;
        border: 1px solid #bfdbfe;
        border-top: none;
        padding: 7px 14px;
    }
    .year-total-left  { display: table-cell; font-size: 10px; font-weight: 700; color: #1e40af; }
    .year-total-right { display: table-cell; text-align: right; font-size: 10px; font-weight: 700; color: #1e40af; }

    /* ── Grade badge ── */
    .grade-badge {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        font-size: 10px;
        font-weight: 700;
        text-align: center;
        line-height: 20px;
    }
    .grade-A { background: #ecfdf5; color: #007a55; }
    .grade-B { background: #ecfeff; color: #0891b2; }
    .grade-C { background: #fff7ed; color: #ff8904; }
    .grade-D, .grade-F { background: #fef2f2; color: #ef4444; }

    /* ── Cumulative block ── */
    .cumulative {
        margin-top: 24px;
        background: #1e3a8a;
        color: #ffffff;
        border-radius: 8px;
        padding: 12px 16px;
        display: table;
        width: 100%;
    }
    .cum-left  { display: table-cell; font-size: 12px; font-weight: 700; vertical-align: middle; }
    .cum-right { display: table-cell; text-align: right; vertical-align: middle; }
    .cum-avg   { font-size: 22px; font-weight: 700; }
    .cum-label { font-size: 9px; color: #bfdbfe; text-transform: uppercase; letter-spacing: 0.08em; }

    /* ── Grading scale key ── */
    .scale-section {
        margin-top: 20px;
        border: 1px solid #e7eaf3;
        border-radius: 8px;
        overflow: hidden;
    }
    .scale-header {
        background: #f9fafb;
        padding: 6px 14px;
        font-size: 9px;
        font-weight: 600;
        color: #6a7282;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .scale-body  { display: table; width: 100%; }
    .scale-row   { display: table-row; }
    .scale-cell  {
        display: table-cell;
        padding: 5px 12px;
        font-size: 9px;
        color: #6a7282;
        border-right: 1px solid #e7eaf3;
        text-align: center;
    }
    .scale-cell:last-child { border-right: none; }

    /* ── Signature / footer ── */
    .footer {
        margin-top: 32px;
        border-top: 1px solid #e7eaf3;
        padding-top: 14px;
        display: table;
        width: 100%;
    }
    .footer-col { display: table-cell; width: 33%; vertical-align: top; }
    .footer-label {
        font-size: 9px;
        color: #99a1af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-top: 1px solid #6a7282;
        padding-top: 4px;
        margin-top: 28px;
    }
    .watermark {
        text-align: center;
        font-size: 9px;
        color: #99a1af;
        margin-top: 16px;
    }

    /* ── Attendance pill ── */
    .att-pill {
        display: inline-block;
        padding: 1px 8px;
        border-radius: 9999px;
        font-size: 9px;
        font-weight: 600;
    }
    .att-good    { background: #ecfdf5; color: #007a55; }
    .att-warning { background: #fff7ed; color: #c2580a; }
    .att-poor    { background: #fef2f2; color: #ef4444; }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 40px 0;
        color: #99a1af;
        font-size: 12px;
    }
</style>
</head>
<body>

{{-- ── Header ── --}}
<div class="header">
    <div class="header-left">
        @if($logoBase64)
        <img src="{{ $logoBase64 }}" alt="School Logo"
             style="width: 44px; height: 44px; border-radius: 6px; object-fit: contain; display: block; margin-bottom: 6px;">
        @endif
        <div class="school-name">{{ $schoolProfile?->school_name ?? tenant('name') ?? 'School' }}</div>
        <div class="school-sub">Official Student Transcript</div>
    </div>
    <div class="header-right">
        <div class="report-title">
            <h1>STUDENT TRANSCRIPT</h1>
            <p>Issued: {{ now()->format('d M Y') }}</p>
            <p>All Academic Records &mdash; Published Exams</p>
        </div>
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
        <div class="info-cell">
            <span class="info-label">Date of Birth</span>
            <span class="info-value">{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</span>
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
            <span class="info-label">Gender</span>
            <span class="info-value">{{ $student->gender ? ucfirst($student->gender) : '—' }}</span>
        </div>
        <div class="info-cell">
            <span class="info-label">Guardian</span>
            <span class="info-value">{{ $student->guardian_name ?? '—' }}</span>
        </div>
    </div>
</div>

{{-- ── Academic Records ── --}}
@if($years->isEmpty())
<p class="empty-state">No published exam results found for this student.</p>
@else

@foreach($years as $yearData)
@php $ay = $yearData['academic_year']; @endphp

{{-- Year header --}}
<div class="year-header">
    <div class="year-header-left">{{ $ay->name }}</div>
    <div class="year-header-right">
        Year Average:
        @if($yearData['year_average'] !== null)
            {{ number_format($yearData['year_average'], 1) }} / 100
            ({{ $yearData['year_grade'] }})
        @else
            &mdash;
        @endif
    </div>
</div>

@foreach($yearData['terms'] as $termData)
@php $term = $termData['term']; @endphp

{{-- Term header --}}
<div class="term-header">
    <div class="term-header-left">{{ $term->name }}</div>
    <div class="term-header-right">
        Attendance:
        @if($termData['attendance_pct'] !== null)
            @php
                $pct = $termData['attendance_pct'];
                $attClass = $pct >= 80 ? 'att-good' : ($pct >= 60 ? 'att-warning' : 'att-poor');
            @endphp
            <span class="att-pill {{ $attClass }}">{{ number_format($pct, 1) }}%</span>
        @else
            <span style="color:#99a1af">No data</span>
        @endif
        &nbsp;&nbsp;
        Term Avg:
        @if($termData['term_average'] !== null)
            {{ number_format($termData['term_average'], 1) }}
            ({{ $termData['term_grade'] }})
        @else
            &mdash;
        @endif
    </div>
</div>

@foreach($termData['exams'] as $examData)
@php $exam = $examData['exam']; @endphp

{{-- Exam label --}}
<div class="exam-label">
    {{ $exam->name }}
    @if($exam->start_date)
        &mdash; {{ $exam->start_date->format('d M Y') }}
        @if($exam->end_date) &ndash; {{ $exam->end_date->format('d M Y') }}@endif
    @endif
</div>

{{-- Results table --}}
@if($examData['results']->isNotEmpty())
<table class="results">
    <thead>
        <tr>
            <th>#</th>
            <th>Subject</th>
            <th class="center" style="width:80px">Marks (/100)</th>
            <th class="center" style="width:50px">Grade</th>
            <th style="width:100px">Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach($examData['results'] as $i => $row)
        <tr>
            <td style="width:24px">{{ $i + 1 }}</td>
            <td>{{ $row['subject'] }}</td>
            <td class="center"><strong>{{ number_format($row['marks'], 1) }}</strong></td>
            <td class="center">
                <span class="grade-badge grade-{{ $row['grade'] }}">{{ $row['grade'] }}</span>
            </td>
            <td>{{ $row['remark'] }}</td>
        </tr>
        @endforeach
    </tbody>
    @if($examData['average'] !== null)
    <tfoot>
        <tr>
            <td colspan="2"><strong>Exam Average</strong></td>
            <td class="center"><strong>{{ number_format($examData['average'], 1) }}</strong></td>
            <td class="center">
                <span class="grade-badge grade-{{ $examData['average_grade'] }}">{{ $examData['average_grade'] }}</span>
            </td>
            <td><strong>{{ $examData['average_remark'] }}</strong></td>
        </tr>
    </tfoot>
    @endif
</table>
@else
<div style="padding:8px 12px; color:#99a1af; font-size:10px; background:#f9fafb;">No marks recorded for this exam.</div>
@endif

@endforeach {{-- exams --}}

@endforeach {{-- terms --}}

{{-- Year cumulative row --}}
<div class="year-total">
    <div class="year-total-left">{{ $ay->name }} &mdash; Cumulative Average</div>
    <div class="year-total-right">
        @if($yearData['year_average'] !== null)
            {{ number_format($yearData['year_average'], 1) }} / 100
            &nbsp;&nbsp;
            <span class="grade-badge grade-{{ $yearData['year_grade'] }}" style="display:inline-block">{{ $yearData['year_grade'] }}</span>
            &nbsp;&nbsp;{{ $yearData['year_remark'] }}
        @else
            &mdash;
        @endif
    </div>
</div>

@endforeach {{-- years --}}

{{-- ── Overall Cumulative ── --}}
<div class="cumulative">
    <div class="cum-left">
        <div class="cum-label">Overall Cumulative Average</div>
        <div style="margin-top:4px; font-size:10px; color:#bfdbfe;">Across all published exams</div>
    </div>
    <div class="cum-right">
        @if($cumulative_average !== null)
        <div class="cum-avg">{{ number_format($cumulative_average, 1) }}<span style="font-size:12px; font-weight:400"> / 100</span></div>
        <div style="font-size:10px; color:#bfdbfe; margin-top:2px">{{ $cumulative_grade }} &mdash; {{ $cumulative_remark }}</div>
        @else
        <div class="cum-avg">&mdash;</div>
        @endif
    </div>
</div>

{{-- ── Grading Scale ── --}}
@if(!empty($scale))
<div class="scale-section">
    <div class="scale-header">Grading Scale</div>
    <div class="scale-body">
        <div class="scale-row">
            @foreach($scale as $band)
            <div class="scale-cell">
                <strong>{{ $band['grade'] }}</strong> &mdash; {{ $band['min'] }}&ndash;{{ $band['max'] }} ({{ $band['remark'] }})
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endif {{-- years not empty --}}

{{-- ── Signature block ── --}}
<div class="footer">
    <div class="footer-col">
        <div class="footer-label">Head Teacher / Principal</div>
    </div>
    <div class="footer-col" style="text-align:center">
        <div class="footer-label">School Registrar</div>
    </div>
    <div class="footer-col" style="text-align:right">
        <div class="footer-label">School Stamp</div>
    </div>
</div>

<div class="watermark">
    Official Transcript &mdash; {{ $schoolProfile?->school_name ?? tenant('name') }} &mdash; Generated by Skolet &middot; {{ now()->format('d M Y, H:i') }}
</div>

</body>
</html>

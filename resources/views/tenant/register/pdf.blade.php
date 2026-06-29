<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Register &mdash; {{ $staff->full_name }} &mdash; {{ $month->format('F Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1f2937; background: #fff; }
        .page { padding: 28px 36px; }

        /* Header */
        .header { display: table; width: 100%; padding-bottom: 14px; border-bottom: 2px solid #e5e7eb; margin-bottom: 18px; }
        .header-left  { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; text-align: right; vertical-align: middle; }
        .school-name  { font-size: 16px; font-weight: 700; color: #111827; }
        .report-title { font-size: 12px; font-weight: 600; color: #6b7280; margin-top: 2px; }
        .period-label { font-size: 10px; color: #9ca3af; margin-top: 2px; }

        /* Staff info */
        .staff-info { display: table; width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .info-cell   { display: table-cell; width: 50%; padding: 8px 12px; border: 1px solid #e5e7eb; }
        .info-label  { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; margin-bottom: 2px; }
        .info-value  { font-size: 11px; color: #374151; font-weight: 600; }

        /* Table */
        .register-table { width: 100%; border-collapse: collapse; }
        .register-table th { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; padding: 7px 10px; text-align: left; border-bottom: 2px solid #e5e7eb; background: #f9fafb; }
        .register-table td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; font-size: 10px; color: #374151; vertical-align: top; }
        .register-table tr:nth-child(even) td { background: #f9fafb; }
        .register-table .date-cell   { width: 90px; white-space: nowrap; color: #374151; font-weight: 600; }
        .register-table .class-cell  { width: 110px; }
        .register-table .subject-cell{ width: 110px; }
        .register-table .topic-cell  { width: 200px; }
        .register-table .notes-cell  { color: #6b7280; }
        .no-entries { text-align: center; padding: 30px; color: #9ca3af; font-size: 11px; }

        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 24px; padding-top: 10px; border-top: 1px solid #e5e7eb; }
        .badge  { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 9px; font-weight: 600; background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="school-name">{{ $profile?->name ?? 'School' }}</div>
            <div class="report-title">Class Register &mdash; {{ $month->format('F Y') }}</div>
        </div>
        <div class="header-right">
            <div class="period-label">Generated {{ now()->format('d M Y') }}</div>
        </div>
    </div>

    {{-- Staff info --}}
    <div class="staff-info">
        <div class="info-cell">
            <div class="info-label">Teacher</div>
            <div class="info-value">{{ $staff->full_name }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">Role</div>
            <div class="info-value">{{ $staff->role_title ?? $staff->position ?? 'Teacher' }}</div>
        </div>
    </div>

    {{-- Register entries --}}
    @if($entries->isEmpty())
    <p class="no-entries">No register entries for {{ $month->format('F Y') }}.</p>
    @else
    <table class="register-table">
        <thead>
            <tr>
                <th class="date-cell">Date</th>
                <th class="class-cell">Class</th>
                <th class="subject-cell">Subject</th>
                <th class="topic-cell">Topic Covered</th>
                <th class="notes-cell">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
            <tr>
                <td class="date-cell">{{ $entry->date->format('d M Y') }} <span style="font-size:8px;color:#9ca3af;">{{ $entry->date->format('D') }}</span></td>
                <td class="class-cell">
                    {{ $entry->schoolClass->name }}
                    @if($entry->section)
                    <span class="badge">{{ $entry->section->name }}</span>
                    @endif
                </td>
                <td class="subject-cell">{{ $entry->subject->name }}</td>
                <td class="topic-cell">{{ $entry->topic_covered }}</td>
                <td class="notes-cell">{{ $entry->notes ?? '&mdash;' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 10px; font-size: 10px; color: #6b7280; text-align: right;">
        Total entries: {{ $entries->count() }}
    </div>
    @endif

    <div class="footer">
        {{ $profile?->name ?? 'SchoolFlow' }} &middot; Class Register &middot; {{ $staff->full_name }} &middot; {{ $month->format('F Y') }} &middot; Generated {{ now()->format('d M Y H:i') }}
    </div>

</div>
</body>
</html>

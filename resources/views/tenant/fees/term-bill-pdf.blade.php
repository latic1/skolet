@php $currencySymbol = $schoolProfile?->currency_symbol ?? '₵'; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
@page { margin: 8mm 12mm; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1a1a1a; background: #fff; }

/* ── Header ── */
.header { display: table; width: 100%; margin-bottom: 3mm; }
.header-logo { display: table-cell; vertical-align: middle; width: 13mm; }
.header-logo img { width: 11mm; height: 11mm; object-fit: contain; }
.header-logo-placeholder {
    width: 11mm; height: 11mm; background: #2a3f6f; border-radius: 3px;
    display: table; text-align: center; vertical-align: middle;
}
.header-logo-placeholder span { display: table-cell; vertical-align: middle; color: #fff; font-size: 10pt; font-weight: bold; }
.header-info { display: table-cell; vertical-align: middle; padding-left: 3mm; }
.school-name { font-size: 11pt; font-weight: bold; color: #1a1a1a; line-height: 1.2; }
.school-motto { font-size: 7pt; color: #555; font-style: italic; margin-top: 0.5mm; }
.school-contact { font-size: 7pt; color: #666; margin-top: 0.5mm; }
.bill-title { font-size: 7.5pt; color: #2a3f6f; font-weight: bold; margin-top: 1mm; }
.header-right { display: table-cell; vertical-align: top; text-align: right; font-size: 7.5pt; color: #555; width: 42mm; }

/* ── Student info strip ── */
.student-strip {
    background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 3px;
    padding: 2.5mm 4mm; margin-bottom: 3mm;
}
.strip-row { display: table; width: 100%; }
.strip-row + .strip-row { border-top: 1px solid #e8e8e8; margin-top: 1.5mm; padding-top: 1.5mm; }
.strip-cell { display: table-cell; font-size: 8pt; vertical-align: top; }
.strip-label { color: #888; font-size: 6.5pt; display: block; }
.strip-value { font-weight: bold; color: #1a1a1a; font-size: 8pt; }

/* ── Fee table ── */
table.fees { width: 100%; border-collapse: collapse; font-size: 8pt; }
table.fees th {
    background: #2a3f6f; color: #fff; padding: 1.8mm 3mm;
    text-align: left; font-size: 7.5pt; font-weight: normal;
}
table.fees td { padding: 1.5mm 3mm; border-bottom: 1px solid #eee; vertical-align: middle; }
table.fees tr:last-child td { border-bottom: none; }
table.fees .right { text-align: right; }

/* ── Totals row ── */
.totals-row td { background: #f9f9f9; font-weight: bold; border-top: 1.5px solid #ccc; }
.arrears-row td { color: #991b1b; }

/* ── Signature area ── */
.sig-area { display: table; width: 100%; margin-top: 4mm; border-top: 1px solid #ddd; padding-top: 3mm; }
.sig-cell { display: table-cell; text-align: center; padding: 0 3mm; }
.sig-line { width: 65%; border-top: 1px solid #333; margin: 10mm auto 1.5mm; }
.sig-label { font-size: 7pt; color: #555; }
.sig-officer-name { font-size: 7pt; font-weight: bold; color: #1a1a1a; margin-top: 1mm; }

/* ── Footer note ── */
.footer-note { font-size: 7pt; color: #999; margin-top: 2mm; text-align: center; }
</style>
</head>
<body>

@php
    $totalOwed        = collect($feeItems)->sum(fn($i) => $i['effective_amount'] ?? (float) $i['fee_structure']->amount);
    $totalPaid        = collect($feeItems)->sum(fn($i) => $i['paid_amount']);
    $totalOutstanding = collect($feeItems)->sum(fn($i) => $i['outstanding']);
    $grandOutstanding = $totalOutstanding + $arrearsTotal;
    $printDate        = now()->format('d M Y');

    $academicYearName = $term?->academicYear?->name ?? '';
    $termLabel        = $term
        ? ($term->name . ($academicYearName ? ' · ' . $academicYearName : ''))
        : 'All Terms';

    $guardian     = $student->guardian_name ?? null;
    $totalInWords = \App\Helpers\NumberToWords::convert($totalOwed);
@endphp

{{-- Header --}}
<div class="header">
    <div class="header-logo">
        @if($logoBase64)
        <img src="{{ $logoBase64 }}" alt="logo">
        @else
        <div class="header-logo-placeholder">
            <span>{{ strtoupper(substr($schoolProfile?->school_name ?? 'S', 0, 1)) }}</span>
        </div>
        @endif
    </div>
    <div class="header-info">
        <div class="school-name">{{ $schoolProfile?->school_name ?? 'School' }}</div>
        @if($schoolProfile?->motto)
        <div class="school-motto">{{ $schoolProfile->motto }}</div>
        @endif
        @php
            $contactParts = array_filter([
                $schoolProfile?->phone ?? null,
                $schoolProfile?->email ?? null,
                $schoolProfile?->address ?? null,
            ]);
        @endphp
        @if(count($contactParts))
        <div class="school-contact">{{ implode(' · ', $contactParts) }}</div>
        @endif
        <div class="bill-title">TERM FEE BILL &mdash; {{ strtoupper($termLabel) }}</div>
    </div>
    <div class="header-right">
        <div>Date: {{ $printDate }}</div>
    </div>
</div>

{{-- Student strip --}}
<div class="student-strip">
    <div class="strip-row">
        <div class="strip-cell" style="width:38%">
            <span class="strip-label">Student Name</span>
            <span class="strip-value">{{ $student->full_name }}</span>
        </div>
        <div class="strip-cell" style="width:18%">
            <span class="strip-label">Adm. No</span>
            <span class="strip-value">{{ $student->admission_no ?? '—' }}</span>
        </div>
        <div class="strip-cell" style="width:22%">
            <span class="strip-label">Class</span>
            <span class="strip-value">{{ $student->schoolClass?->name ?? '—' }}{{ $student->section ? ' · ' . $student->section->name : '' }}</span>
        </div>
        <div class="strip-cell" style="width:22%;text-align:right">
            <span class="strip-label">Term / Year</span>
            <span class="strip-value">{{ $termLabel }}</span>
        </div>
    </div>
    @if($guardian)
    <div class="strip-row">
        <div class="strip-cell" style="width:100%">
            <span class="strip-label">Guardian / Parent</span>
            <span class="strip-value">{{ preg_replace('/[\r\n]+/', ', ', trim($guardian)) }}</span>
        </div>
    </div>
    @endif
</div>

{{-- Fee items table --}}
@if(empty($feeItems))
<p style="color:#999;font-size:8pt;text-align:center;padding:4mm 0;">No fee items applicable for this term.</p>
@else
<table class="fees">
    <thead>
        <tr>
            <th style="width:70%">Fee Item</th>
            <th class="right" style="width:30%">Amount</th>
        </tr>
    </thead>
    <tbody>
        @php
            $feeItemsGrouped = collect($feeItems)->groupBy(fn($i) => $i['fee_structure']->fee_bundle_id ?? '__standalone__');
        @endphp
        @foreach($feeItemsGrouped as $groupKey => $groupItems)
            @if($groupKey !== '__standalone__')
            @php $bundleName = $groupItems->first()['fee_structure']->bundle?->name ?? 'Bundle'; @endphp
            <tr style="background:#eef2ff;">
                <td colspan="2" style="font-weight:bold;font-size:7.5pt;color:#2a3f6f;padding:1.2mm 3mm;letter-spacing:0.03em;">
                    {{ $bundleName }}
                </td>
            </tr>
            @endif
            @foreach($groupItems as $item)
            @php
                $fs              = $item['fee_structure'];
                $effectiveAmount = $item['effective_amount'] ?? (float) $fs->amount;
                $inBundle        = $groupKey !== '__standalone__';
            @endphp
            <tr>
                <td style="{{ $inBundle ? 'padding-left:5mm;' : '' }}">{{ $fs->fee_item }}</td>
                <td class="right">{{ format_money($effectiveAmount, $currencySymbol) }}</td>
            </tr>
            @endforeach
        @endforeach

        @if($arrearsTotal > 0)
        <tr class="arrears-row">
            <td style="font-style:italic;">Arrears (previous terms)</td>
            <td class="right">{{ format_money($arrearsTotal, $currencySymbol) }}</td>
        </tr>
        @endif

        <tr class="totals-row">
            <td>
                Total: {{ format_money($totalOwed, $currencySymbol) }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Paid: <span style="color:#065f46;">{{ format_money($totalPaid, $currencySymbol) }}</span>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Balance: <span style="{{ $grandOutstanding > 0 ? 'color:#991b1b;' : 'color:#065f46;' }}">{{ format_money($grandOutstanding, $currencySymbol) }}</span>
            </td>
            <td></td>
        </tr>
    </tbody>
</table>
@endif

{{-- Amount in words --}}
<div style="margin-top:2mm;padding:1.5mm 3mm;border:0.5pt solid #ddd;border-radius:2px;font-size:7.5pt;">
    <span style="color:#888;">Amount in Words:&nbsp;</span>
    <span style="font-style:italic;font-weight:bold;color:#1a2d5a;">{{ $totalInWords }} Only</span>
</div>

{{-- Signature --}}
<div class="sig-area">
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Account Officer Signature</div>
        @if($accountOfficer)
        <div class="sig-officer-name">{{ $accountOfficer }}</div>
        @endif
    </div>
</div>

{{-- Footer note --}}
<div class="footer-note">
    This bill is computer-generated. Please keep for your records.
    @if($schoolProfile?->phone || $schoolProfile?->email)
    For queries: {{ implode(' · ', array_filter([$schoolProfile?->phone ?? null, $schoolProfile?->email ?? null])) }}
    @endif
</div>

</body>
</html>

@php $currencySymbol = $schoolProfile?->currency_symbol ?? '₵'; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1a1a1a; background: #fff; }

/* ── Copy label banner ── */
.copy-banner {
    background: #2a3f6f;
    color: #fff;
    text-align: center;
    font-size: 7pt;
    font-weight: bold;
    letter-spacing: 3px;
    text-transform: uppercase;
    padding: 1.5mm 0;
}

.copy { padding: 6mm 12mm 6mm 12mm; height: 140mm; overflow: hidden; }

.cut-line {
    border-top: 1.5px dashed #aaa;
    margin: 0 10mm;
    position: relative;
    text-align: center;
}
.cut-line span {
    display: inline-block;
    background: #fff;
    padding: 0 6px;
    font-size: 7pt;
    color: #aaa;
    position: relative;
    top: -7px;
}

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
.header-right-date { color: #555; }
.header-right-no { color: #2a3f6f; font-weight: bold; margin-top: 1mm; font-size: 7pt; }

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
table.fees .center { text-align: center; }

/* ── Status badges ── */
.badge { display: inline-block; padding: 0.5mm 2.5mm; border-radius: 8px; font-size: 7pt; font-weight: bold; }
.badge-paid    { background: #d1fae5; color: #065f46; }
.badge-partial { background: #fef3c7; color: #92400e; }
.badge-unpaid  { background: #fee2e2; color: #991b1b; }
.badge-overdue { background: #fee2e2; color: #991b1b; }
.badge-annual  { background: #ede9fe; color: #5b21b6; }

/* ── Totals row ── */
.totals-row td { background: #f9f9f9; font-weight: bold; border-top: 1.5px solid #ccc; }
.arrears-row td { color: #991b1b; }

/* ── Signature area ── */
.sig-area { display: table; width: 100%; margin-top: 3mm; border-top: 1px solid #ddd; padding-top: 3mm; }
.sig-cell { display: table-cell; width: 50%; text-align: center; padding: 0 3mm; }
.sig-stamp-box { height: 13mm; border: 1px dashed #bbb; border-radius: 3px; margin-bottom: 1.5mm; }
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

    // Use literal middle dot to avoid HTML entity escaping inside {{ }}
    $academicYearName = $term?->academicYear?->name ?? '';
    $termLabel        = $term
        ? ($term->name . ($academicYearName ? ' · ' . $academicYearName : ''))
        : 'All Terms';

    $guardian = $student->guardian_name ?? null;
@endphp

@for ($copy = 1; $copy <= 2; $copy++)
@if ($copy === 2)
<div class="cut-line"><span>&#9988; &nbsp; cut here &nbsp; &#9988;</span></div>
@endif

{{-- Copy label band --}}
<div class="copy-banner">
    @if($copy === 1) &#8212; School Copy &#8212; @else &#8212; Parent Copy &#8212; @endif
</div>

<div class="copy">

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
            <div class="header-right-date">Date: {{ $printDate }}</div>
        </div>
    </div>

    {{-- Student strip — row 1: core identity --}}
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
                <span class="strip-label">Received From (Guardian / Parent)</span>
                <span class="strip-value">{{ $guardian }}</span>
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
                <th style="width:35%">Fee Item</th>
                <th style="width:10%">Type</th>
                <th class="right" style="width:14%">Amount</th>
                <th class="right" style="width:14%">Paid</th>
                <th class="right" style="width:14%">Balance</th>
                <th class="center" style="width:13%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $seenBundleIds = [];
                $feeItemsGrouped = collect($feeItems)->groupBy(fn($i) => $i['fee_structure']->fee_bundle_id ?? '__standalone__');
            @endphp
            @foreach($feeItemsGrouped as $groupKey => $groupItems)
                @if($groupKey !== '__standalone__')
                @php $bundleName = $groupItems->first()['fee_structure']->bundle?->name ?? 'Bundle'; @endphp
                <tr style="background:#eef2ff;">
                    <td colspan="6" style="font-weight:bold;font-size:7.5pt;color:#2a3f6f;padding:1.2mm 3mm;letter-spacing:0.03em;">
                        {{ $bundleName }}
                    </td>
                </tr>
                @endif
                @foreach($groupItems as $item)
                @php
                    $fs              = $item['fee_structure'];
                    $status          = $item['status'];
                    $badgeClass      = match($status) {
                        'paid'    => 'badge-paid',
                        'partial' => 'badge-partial',
                        default   => $status === 'overdue' ? 'badge-overdue' : 'badge-unpaid',
                    };
                    $isAnnual        = ($fs->billing_cycle ?? 'term') === 'annual';
                    $effectiveAmount = $item['effective_amount'] ?? (float) $fs->amount;
                    $inBundle        = $groupKey !== '__standalone__';
                @endphp
                <tr>
                    <td style="{{ $inBundle ? 'padding-left:5mm;' : '' }}">{{ $fs->fee_item }}</td>
                    <td>
                        <span class="badge {{ $isAnnual ? 'badge-annual' : '' }}" style="{{ $isAnnual ? '' : 'background:#e8f4fd;color:#1e40af;' }}">
                            {{ $isAnnual ? 'Annual' : 'Term' }}
                        </span>
                    </td>
                    <td class="right">{{ format_money($effectiveAmount, $currencySymbol) }}</td>
                    <td class="right">{{ format_money($item['paid_amount'], $currencySymbol) }}</td>
                    <td class="right" style="{{ $item['outstanding'] > 0 ? 'color:#991b1b;' : '' }}">{{ format_money($item['outstanding'], $currencySymbol) }}</td>
                    <td class="center"><span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span></td>
                </tr>
                @endforeach
            @endforeach

            @if($arrearsTotal > 0)
            <tr class="arrears-row">
                <td colspan="4" style="font-style:italic;">Arrears (previous terms)</td>
                <td class="right">{{ format_money($arrearsTotal, $currencySymbol) }}</td>
                <td></td>
            </tr>
            @endif

            <tr class="totals-row">
                <td colspan="2">Total</td>
                <td class="right">{{ format_money($totalOwed, $currencySymbol) }}</td>
                <td class="right">{{ format_money($totalPaid, $currencySymbol) }}</td>
                <td class="right" style="{{ $grandOutstanding > 0 ? 'color:#991b1b;' : 'color:#065f46;' }}">{{ format_money($grandOutstanding, $currencySymbol) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endif

    {{-- Signature / stamp area --}}
    <div class="sig-area">
        <div class="sig-cell">
            <div class="sig-stamp-box"></div>
            <div class="sig-label">Official Stamp</div>
        </div>
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
        @if($schoolProfile?->phone || $schoolProfile?->email || $schoolProfile?->address)
        For queries: {{ implode(' · ', array_filter([$schoolProfile?->phone ?? null, $schoolProfile?->email ?? null])) }}
        @endif
    </div>

</div>
@endfor

</body>
</html>

@php $currencySymbol = $schoolProfile?->currency_symbol ?? '₵'; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1a1a1a; background: #fff; }

.copy { padding: 14mm 14mm 10mm 14mm; height: 135mm; overflow: hidden; }

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

/* Header */
.header { display: table; width: 100%; margin-bottom: 6mm; }
.header-logo { display: table-cell; vertical-align: middle; width: 14mm; }
.header-logo img { width: 12mm; height: 12mm; object-fit: contain; }
.header-info { display: table-cell; vertical-align: middle; padding-left: 3mm; }
.school-name { font-size: 12pt; font-weight: bold; color: #1a1a1a; line-height: 1.2; }
.bill-title { font-size: 8pt; color: #666; margin-top: 1mm; }
.header-right { display: table-cell; vertical-align: top; text-align: right; font-size: 7.5pt; color: #555; width: 50mm; }

/* Student info strip */
.student-strip { background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 3px; padding: 3mm 4mm; margin-bottom: 4mm; display: table; width: 100%; }
.strip-cell { display: table-cell; font-size: 8pt; vertical-align: top; }
.strip-label { color: #888; font-size: 7pt; display: block; }
.strip-value { font-weight: bold; color: #1a1a1a; }

/* Fee table */
table.fees { width: 100%; border-collapse: collapse; font-size: 8pt; }
table.fees th {
    background: #2a3f6f;
    color: #fff;
    padding: 2mm 3mm;
    text-align: left;
    font-size: 7.5pt;
    font-weight: normal;
}
table.fees td { padding: 1.8mm 3mm; border-bottom: 1px solid #eee; vertical-align: middle; }
table.fees tr:last-child td { border-bottom: none; }
table.fees .right { text-align: right; }
table.fees .center { text-align: center; }

/* Status badges */
.badge { display: inline-block; padding: 0.5mm 2.5mm; border-radius: 8px; font-size: 7pt; font-weight: bold; }
.badge-paid    { background: #d1fae5; color: #065f46; }
.badge-partial { background: #fef3c7; color: #92400e; }
.badge-unpaid  { background: #fee2e2; color: #991b1b; }
.badge-overdue { background: #fee2e2; color: #991b1b; }
.badge-annual  { background: #ede9fe; color: #5b21b6; }

/* Totals row */
.totals-row td { background: #f9f9f9; font-weight: bold; border-top: 1.5px solid #ccc; }
.arrears-row td { color: #991b1b; }

/* Footer note */
.footer-note { font-size: 7pt; color: #999; margin-top: 3mm; text-align: center; }
</style>
</head>
<body>

@php
    $totalOwed       = collect($feeItems)->sum(fn($i) => (float) $i['fee_structure']->amount);
    $totalPaid       = collect($feeItems)->sum(fn($i) => $i['paid_amount']);
    $totalOutstanding = collect($feeItems)->sum(fn($i) => $i['outstanding']);
    $grandOutstanding = $totalOutstanding + $arrearsTotal;
    $printDate        = now()->format('d M Y');
    $termLabel        = $term
        ? ($term->name . ($term->academicYear ? ' &middot; ' . $term->academicYear->name : ''))
        : 'All Terms';
@endphp

@for ($copy = 1; $copy <= 2; $copy++)
@if ($copy === 2)
<div class="cut-line"><span>✂ &nbsp; cut here &nbsp; ✂</span></div>
@endif

<div class="copy">

    {{-- Header --}}
    <div class="header">
        <div class="header-logo">
            @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="logo">
            @else
            <div style="width:12mm;height:12mm;background:#2a3f6f;border-radius:3px;display:flex;align-items:center;justify-content:center;">
                <span style="color:#fff;font-size:10pt;font-weight:bold;">{{ strtoupper(substr($schoolProfile?->school_name ?? 'S', 0, 1)) }}</span>
            </div>
            @endif
        </div>
        <div class="header-info">
            <div class="school-name">{{ $schoolProfile?->school_name ?? 'School' }}</div>
            <div class="bill-title">TERM FEE BILL &mdash; {{ strtoupper($termLabel) }}</div>
        </div>
        <div class="header-right">
            <div>Date: {{ $printDate }}</div>
            @if($copy === 1)
            <div style="margin-top:1mm;color:#2a3f6f;font-weight:bold;">School Copy</div>
            @else
            <div style="margin-top:1mm;color:#2a3f6f;font-weight:bold;">Parent Copy</div>
            @endif
        </div>
    </div>

    {{-- Student strip --}}
    <div class="student-strip">
        <div class="strip-cell" style="width:40%">
            <span class="strip-label">Student Name</span>
            <span class="strip-value">{{ $student->full_name }}</span>
        </div>
        <div class="strip-cell" style="width:20%">
            <span class="strip-label">Admission No</span>
            <span class="strip-value">{{ $student->admission_no ?? '&mdash;' }}</span>
        </div>
        <div class="strip-cell" style="width:20%">
            <span class="strip-label">Class</span>
            <span class="strip-value">{{ $student->schoolClass?->name ?? '&mdash;' }}{{ $student->section ? ' &middot; ' . $student->section->name : '' }}</span>
        </div>
        <div class="strip-cell" style="width:20%;text-align:right">
            <span class="strip-label">Term</span>
            <span class="strip-value">{{ $term?->name ?? 'All' }}</span>
        </div>
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
            @foreach($feeItems as $item)
            @php
                $fs     = $item['fee_structure'];
                $status = $item['status'];
                $badgeClass = match($status) {
                    'paid'    => 'badge-paid',
                    'partial' => 'badge-partial',
                    default   => $status === 'overdue' ? 'badge-overdue' : 'badge-unpaid',
                };
                $isAnnual = ($fs->billing_cycle ?? 'term') === 'annual';
            @endphp
            <tr>
                <td>{{ $fs->fee_item }}</td>
                <td><span class="badge {{ $isAnnual ? 'badge-annual' : '' }}" style="{{ $isAnnual ? '' : 'background:#e8f4fd;color:#1e40af;' }}">{{ $isAnnual ? 'Annual' : 'Term' }}</span></td>
                <td class="right">{{ format_money((float) $fs->amount, $currencySymbol) }}</td>
                <td class="right">{{ format_money($item['paid_amount'], $currencySymbol) }}</td>
                <td class="right" style="{{ $item['outstanding'] > 0 ? 'color:#991b1b;' : '' }}">{{ format_money($item['outstanding'], $currencySymbol) }}</td>
                <td class="center"><span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span></td>
            </tr>
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

    <div class="footer-note">
        {{ $schoolProfile?->address ?? '' }}{{ ($schoolProfile?->address && $schoolProfile?->phone) ? ' &middot; ' : '' }}{{ $schoolProfile?->phone ?? '' }}
        @if($schoolProfile?->address || $schoolProfile?->phone) <br> @endif
        This bill is computer-generated. Please keep it for your records.
    </div>

</div>
@endfor

</body>
</html>

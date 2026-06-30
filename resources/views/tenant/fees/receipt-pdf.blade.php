<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9pt;
    color: #111;
    background: #fff;
}

@page { margin: 0; size: A4 portrait; }

.copy {
    width: 210mm;
    height: 143mm;
    padding: 6mm 8mm;
    page-break-inside: avoid;
    overflow: hidden;
}

.cut-line {
    width: 210mm;
    height: 4mm;
    display: flex;
    align-items: center;
    justify-content: center;
    border-top: 1px dashed #bbb;
    line-height: 0;
}
.cut-line span {
    background: #fff;
    padding: 0 6mm;
    font-size: 7pt;
    color: #aaa;
    letter-spacing: 0.05em;
}

.copy-banner {
    background: #2a3f6f;
    color: #fff;
    text-align: center;
    font-size: 7.5pt;
    font-weight: bold;
    letter-spacing: 0.15em;
    padding: 2.5mm 0;
    border-radius: 2px;
    margin-bottom: 4mm;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 3mm;
    border-bottom: 1.5pt solid #2a3f6f;
    padding-bottom: 2.5mm;
}

.school-block {
    display: flex;
    align-items: flex-start;
    gap: 3mm;
    flex: 1;
}

.school-logo {
    width: 15mm;
    height: 15mm;
    object-fit: contain;
}

.school-name {
    font-size: 11pt;
    font-weight: bold;
    text-transform: uppercase;
    color: #1a2d5a;
    line-height: 1.2;
}

.school-motto {
    font-style: italic;
    font-size: 7.5pt;
    color: #555;
    margin-top: 1mm;
}

.school-contact {
    font-size: 7pt;
    color: #666;
    margin-top: 1.5mm;
}

.receipt-meta {
    text-align: right;
    min-width: 48mm;
}

.receipt-title {
    font-size: 11pt;
    font-weight: bold;
    color: #2a3f6f;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.receipt-meta-rows {
    margin-top: 1.5mm;
    font-size: 7.5pt;
}

.receipt-meta-rows .rm-row {
    display: flex;
    justify-content: flex-end;
    gap: 2mm;
    padding: 0.3mm 0;
}

.rm-label { color: #666; }
.rm-value  { font-weight: bold; color: #111; }

.info-row {
    border-bottom: 0.5pt solid #eee;
    padding: 1.2mm 0;
    display: flex;
    gap: 2mm;
    align-items: baseline;
}

.info-label {
    font-size: 7.5pt;
    color: #666;
    white-space: nowrap;
    flex-shrink: 0;
}

.info-value {
    font-size: 8pt;
    font-weight: bold;
    color: #111;
}

.fee-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 2mm;
}

.fee-table th {
    font-size: 7pt;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #fff;
    background: #2a3f6f;
    padding: 1.5mm 3mm;
    text-align: left;
    font-weight: bold;
}

.fee-table th.amt { text-align: right; }

.fee-table td {
    font-size: 8pt;
    padding: 1mm 3mm;
    border-bottom: 0.5pt solid #eee;
    vertical-align: middle;
}

.fee-table td.amt {
    text-align: right;
    font-weight: bold;
    white-space: nowrap;
}

.fee-table tfoot td {
    border-top: 1pt solid #2a3f6f;
    border-bottom: none;
    font-weight: bold;
    background: #f0f4ff;
    font-size: 8.5pt;
    padding: 1.5mm 3mm;
}

.summary {
    margin-top: 2mm;
    border: 0.5pt solid #ddd;
    border-radius: 2px;
    padding: 1.5mm 3mm;
}

.s-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0 8mm;
}

.s-cell {
    font-size: 7.5pt;
    padding: 0.5mm 0;
    min-width: 45%;
}

.s-lbl { color: #666; }
.s-val { font-weight: bold; color: #111; }

.words-row {
    font-size: 7.5pt;
    margin-top: 1mm;
    padding-top: 1mm;
    border-top: 0.5pt solid #eee;
}

.words-row .s-lbl { color: #666; }
.words-row .s-val { font-style: italic; color: #1a2d5a; font-weight: bold; }

.balance-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1.2mm;
    padding-top: 1.2mm;
    border-top: 0.5pt solid #ddd;
}

.b-lbl { font-size: 7.5pt; color: #666; }
.b-right { display: flex; align-items: center; gap: 2mm; }
.b-amount { font-size: 9pt; font-weight: bold; }
.b-amount.zero  { color: #2a7a4e; }
.b-amount.owing { color: #c0392b; }

.p-tag {
    font-size: 6.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0.8mm 2mm;
    border-radius: 2px;
}

.p-tag.full    { background: #d4edda; color: #2a7a4e; }
.p-tag.partial { background: #fde8e8; color: #c0392b; }

.footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 2.5mm;
    padding-top: 2mm;
    border-top: 0.5pt solid #ddd;
}

.officer-block .o-lbl { font-size: 7pt; color: #666; }
.officer-block .o-name { font-size: 7.5pt; font-weight: bold; color: #111; margin-top: 0.5mm; }

.sig-boxes { display: flex; gap: 4mm; }

.sig-box { text-align: center; width: 28mm; }

.sig-area {
    height: 9mm;
    width: 100%;
    border: 0.5pt dashed #bbb;
    border-radius: 2px;
    margin-bottom: 0.8mm;
}

.sig-cap { font-size: 6pt; color: #999; }
</style>
</head>
<body>

@php
    $currency = $schoolProfile?->currency_symbol ?? 'GHS';
    $fmt = fn(float $v) => $currency . ' ' . number_format($v, 2);

    $contactParts = array_filter([
        $schoolProfile?->phone  ? 'Tel: ' . $schoolProfile->phone  : null,
        $schoolProfile?->email  ? 'Email: ' . $schoolProfile->email : null,
        $schoolProfile?->address ?? null,
    ]);
    $contactLine   = implode(' · ', $contactParts);
    $isFullPayment = $paymentLabel === 'Full Payment';
@endphp

@for($copy = 1; $copy <= 2; $copy++)
<div class="copy">

    {{-- Header --}}
    <div class="header">
        <div class="school-block">
            @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="" class="school-logo">
            @endif
            <div>
                <div class="school-name">{{ $schoolProfile?->school_name ?? 'School Name' }}</div>
                @if($schoolProfile?->motto)
                <div class="school-motto">{{ $schoolProfile->motto }}</div>
                @endif
                @if($contactLine)
                <div class="school-contact">{{ $contactLine }}</div>
                @endif
            </div>
        </div>

        <div class="receipt-meta">
            <div class="receipt-title">Official Receipt</div>
            <div class="receipt-meta-rows">
                <div class="rm-row"><span class="rm-label">No:&nbsp;</span><span class="rm-value">{{ $receiptNumber }}</span></div>
                @if($student?->admission_no)
                <div class="rm-row"><span class="rm-label">Student ID:&nbsp;</span><span class="rm-value">{{ $student->admission_no }}</span></div>
                @endif
                <div class="rm-row"><span class="rm-label">Date:&nbsp;</span><span class="rm-value">{{ $paidAt->format('d/m/Y g:iA') }}</span></div>
            </div>
        </div>
    </div>

    {{-- Received From / Description --}}
    <div class="info-row">
        <span class="info-label">Received From:</span>
        <span class="info-value">{{ $student?->full_name ?? '—' }}</span>
    </div>
    <div class="info-row" style="margin-bottom:1.5mm;">
        <span class="info-label">Amount in respect of:</span>
        <span class="info-value">{{ $description }}</span>
    </div>

    {{-- Fee items table --}}
    <table class="fee-table">
        <thead>
            <tr>
                <th>Fee Item</th>
                <th class="amt">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeLines as $line)
            <tr>
                <td>{{ $line['fee_item'] }}</td>
                <td class="amt">{{ $fmt($line['amount']) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total Amount Paid</td>
                <td class="amt">{{ $fmt($totalAmount) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Summary --}}
    <div class="summary">
        <div class="s-grid">
            <div class="s-cell"><span class="s-lbl">Pay Method: </span><span class="s-val">{{ ucfirst($method) }}</span></div>
            <div class="s-cell"><span class="s-lbl">Academic Year: </span><span class="s-val">{{ $academicYearName ?: '—' }}</span></div>
            <div class="s-cell"><span class="s-lbl">Class: </span><span class="s-val">{{ $className ?: '—' }}</span></div>
            <div class="s-cell"><span class="s-lbl">Date: </span><span class="s-val">{{ $paidAt->format('d/m/Y') }}</span></div>
            <div class="s-cell"><span class="s-lbl">Amount: </span><span class="s-val">{{ $fmt($totalAmount) }}</span></div>
        </div>
        <div class="words-row">
            <span class="s-lbl">Amount in Words: </span>
            <span class="s-val">{{ $amountInWords }}</span>
        </div>
        <div class="balance-row">
            <span class="b-lbl">Remaining Balance:</span>
            <div class="b-right">
                <span class="b-amount {{ $currentBalance <= 0 ? 'zero' : 'owing' }}">{{ $fmt($currentBalance) }}</span>
                <span class="p-tag {{ $isFullPayment ? 'full' : 'partial' }}">{{ $paymentLabel }}</span>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="officer-block">
            <div class="o-lbl">Accounts Officer</div>
            <div class="o-name">{{ $accountOfficer ?? '________________________' }}</div>
        </div>
        <div class="sig-boxes">
            <div class="sig-box">
                <div class="sig-area"></div>
                <div class="sig-cap">Signature</div>
            </div>
            <div class="sig-box">
                <div class="sig-area"></div>
                <div class="sig-cap">Official Stamp</div>
            </div>
        </div>
    </div>

</div>
@if($copy === 1)
<div class="cut-line"><span>✂ CUT HERE</span></div>
@endif
@endfor

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt #{{ $receiptNo }}</title>
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
    .school-logo {
        width: 44px;
        height: 44px;
        object-fit: contain;
        margin-right: 10px;
        vertical-align: middle;
    }
    .school-info {
        display: inline-block;
        vertical-align: middle;
    }
    .school-name {
        font-size: 17px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 2px;
    }
    .school-sub {
        font-size: 10px;
        color: #6a7282;
    }
    .receipt-title {
        text-align: right;
    }
    .receipt-title h1 {
        font-size: 20px;
        font-weight: 700;
        color: #2563eb;
        letter-spacing: -0.5px;
        margin-bottom: 4px;
    }
    .receipt-no {
        font-size: 10px;
        color: #6a7282;
        margin-bottom: 2px;
    }
    .receipt-date {
        font-size: 10px;
        color: #99a1af;
    }

    /* ── Status badge ── */
    .status-banner {
        background: #ecfdf5;
        border: 1px solid #d0fae5;
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .status-dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .status-text {
        font-size: 11px;
        font-weight: 700;
        color: #007a55;
    }

    /* ── Detail blocks ── */
    .section-heading {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #99a1af;
        margin-bottom: 8px;
    }
    .detail-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .detail-table tr td {
        padding: 7px 0;
        border-bottom: 1px solid #e7eaf3;
        font-size: 11px;
        vertical-align: top;
    }
    .detail-table tr:last-child td {
        border-bottom: none;
    }
    .detail-label {
        color: #6a7282;
        width: 38%;
    }
    .detail-value {
        color: #101828;
        font-weight: 500;
    }

    /* ── Amount box ── */
    .amount-box {
        background: #f9fafb;
        border: 1px solid #e7eaf3;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .amount-label {
        font-size: 11px;
        color: #6a7282;
    }
    .amount-value {
        font-size: 22px;
        font-weight: 700;
        color: #101828;
    }

    /* ── Two-column grid ── */
    .two-col {
        display: table;
        width: 100%;
        margin-bottom: 20px;
    }
    .col-left, .col-right {
        display: table-cell;
        width: 50%;
        vertical-align: top;
    }
    .col-right {
        padding-left: 20px;
    }
    .detail-box {
        border: 1px solid #e7eaf3;
        border-radius: 8px;
        overflow: hidden;
    }
    .detail-box-header {
        background: #f9fafb;
        padding: 8px 14px;
        border-bottom: 1px solid #e7eaf3;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6a7282;
    }
    .detail-box-body {
        padding: 12px 14px;
    }
    .detail-box-body p {
        font-size: 11px;
        color: #101828;
        margin-bottom: 4px;
    }
    .detail-box-body p:last-child {
        margin-bottom: 0;
    }
    .detail-box-body .muted {
        color: #6a7282;
        font-size: 10px;
    }

    /* ── Method badge ── */
    .method-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 700;
    }
    .method-cash {
        background: #f9fafb;
        color: #6a7282;
        border: 1px solid #e7eaf3;
    }
    .method-paystack {
        background: #e6faff;
        color: #0891b2;
        border: 1px solid #cffafe;
    }

    /* ── Footer ── */
    .footer {
        margin-top: 32px;
        padding-top: 16px;
        border-top: 1px solid #e7eaf3;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .footer-note {
        font-size: 9px;
        color: #99a1af;
        max-width: 55%;
    }
    .signature-block {
        text-align: center;
    }
    .signature-line {
        width: 130px;
        border-top: 1px solid #364153;
        margin: 0 auto 4px;
    }
    .signature-label {
        font-size: 9px;
        color: #6a7282;
        text-align: center;
    }
</style>
</head>
<body>

{{-- ── Header ── --}}
<div class="header">
    <div>
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="school-logo" alt="School Logo">
        @endif
        <div class="school-info">
            <div class="school-name">{{ $schoolProfile?->school_name ?? tenant('name') ?? 'School' }}</div>
            @if($schoolProfile?->address)
                <div class="school-sub">{{ $schoolProfile->address }}</div>
            @endif
            @if($schoolProfile?->phone || $schoolProfile?->email)
                <div class="school-sub">
                    @if($schoolProfile->phone){{ $schoolProfile->phone }}@endif
                    @if($schoolProfile->phone && $schoolProfile->email) &nbsp;·&nbsp; @endif
                    @if($schoolProfile->email){{ $schoolProfile->email }}@endif
                </div>
            @endif
        </div>
    </div>
    <div class="receipt-title">
        <h1>RECEIPT</h1>
        <div class="receipt-no"># {{ $receiptNo }}</div>
        <div class="receipt-date">{{ $payment->paid_at?->format('d M Y, h:i A') }}</div>
    </div>
</div>

{{-- ── Paid banner ── --}}
<div class="status-banner">
    <span class="status-dot"></span>
    <span class="status-text">PAYMENT CONFIRMED</span>
</div>

{{-- ── Amount box ── --}}
<div class="amount-box">
    <div>
        <div class="amount-label">Amount Paid</div>
        <div style="font-size: 10px; color: #6a7282; margin-top: 2px;">{{ $payment->feeStructure?->fee_item ?? 'Fee Payment' }}</div>
    </div>
    <div class="amount-value">{{ number_format((float) $payment->amount, 2) }}</div>
</div>

{{-- ── Two-column: Student | Payment info ── --}}
<div class="two-col">
    <div class="col-left">
        <div class="detail-box">
            <div class="detail-box-header">Student Details</div>
            <div class="detail-box-body">
                <p style="font-weight: 700; font-size: 12px;">{{ $payment->student?->full_name ?? '—' }}</p>
                <p class="muted">Adm No: {{ $payment->student?->admission_no ?? '—' }}</p>
                <p class="muted">{{ $payment->student?->schoolClass?->name ?? '' }}{{ $payment->student?->section ? ' · ' . $payment->student->section->name : '' }}</p>
            </div>
        </div>
    </div>
    <div class="col-right">
        <div class="detail-box">
            <div class="detail-box-header">Payment Details</div>
            <div class="detail-box-body">
                <p>
                    Method:&nbsp;
                    @if($payment->payment_method === 'paystack')
                        <span class="method-badge method-paystack">Paystack</span>
                    @else
                        <span class="method-badge method-cash">Cash</span>
                    @endif
                </p>
                @if($payment->paystack_ref)
                    <p class="muted" style="margin-top: 4px;">Ref: {{ $payment->paystack_ref }}</p>
                @endif
                @if($payment->recordedBy)
                    <p class="muted" style="margin-top: 4px;">Recorded by: {{ $payment->recordedBy->name }}</p>
                @endif
                @php
                    $fs = $payment->feeStructure;
                @endphp
                @if($fs?->term)
                    <p class="muted" style="margin-top: 4px;">Term: {{ $fs->term->name }}{{ $fs->term->academicYear ? ' (' . $fs->term->academicYear->name . ')' : '' }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Fee details table ── --}}
<div class="section-heading">Fee Breakdown</div>
<table class="detail-table">
    <tr>
        <td class="detail-label">Fee Item</td>
        <td class="detail-value">{{ $payment->feeStructure?->fee_item ?? '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label">Total Fee Amount</td>
        <td class="detail-value">{{ number_format((float) ($payment->feeStructure?->amount ?? 0), 2) }}</td>
    </tr>
    <tr>
        <td class="detail-label">Amount Paid (this receipt)</td>
        <td class="detail-value" style="color: #007a55; font-weight: 700;">{{ number_format((float) $payment->amount, 2) }}</td>
    </tr>
    @if($fs?->due_date)
    <tr>
        <td class="detail-label">Due Date</td>
        <td class="detail-value">{{ $fs->due_date->format('d M Y') }}</td>
    </tr>
    @endif
</table>

{{-- ── Footer ── --}}
<div class="footer">
    <div class="footer-note">
        This is an official fee receipt issued by {{ $schoolProfile?->school_name ?? 'the school' }}.
        Please keep this receipt for your records.
        For queries, contact the school's accounts office.
    </div>
    <div class="signature-block">
        <div class="signature-line"></div>
        <div class="signature-label">Authorised Signature</div>
    </div>
</div>

</body>
</html>

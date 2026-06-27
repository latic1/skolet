<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice — {{ $tenant->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #ffffff; }

        .page { padding: 40px 48px; }

        .header { display: table; width: 100%; padding-bottom: 24px; border-bottom: 2px solid #2563eb; margin-bottom: 28px; }
        .header-left { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; text-align: right; vertical-align: middle; }

        .brand { font-size: 22px; font-weight: 700; color: #1e3a8a; letter-spacing: -0.5px; }
        .brand-sub { font-size: 11px; color: #6b7280; margin-top: 2px; }

        .invoice-title { font-size: 28px; font-weight: 700; color: #111827; letter-spacing: -1px; }
        .invoice-number { font-size: 11px; color: #6b7280; margin-top: 3px; }

        .meta-grid { display: table; width: 100%; margin-bottom: 28px; }
        .meta-col { display: table-cell; width: 50%; vertical-align: top; }

        .meta-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #9ca3af; margin-bottom: 4px; }
        .meta-value { font-size: 13px; color: #111827; font-weight: 500; }
        .meta-value-muted { font-size: 12px; color: #6b7280; }

        .divider { height: 1px; background: #e5e7eb; margin: 24px 0; }

        .line-items { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .line-items thead tr { background: #f3f4f6; }
        .line-items th { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; padding: 10px 12px; text-align: left; }
        .line-items th.right { text-align: right; }
        .line-items td { padding: 12px; border-bottom: 1px solid #f3f4f6; font-size: 12px; color: #374151; }
        .line-items td.right { text-align: right; }

        .total-row { display: table; width: 100%; margin-top: 8px; }
        .total-spacer { display: table-cell; width: 60%; }
        .total-box { display: table-cell; width: 40%; }

        .total-inner { background: #1e3a8a; color: #ffffff; border-radius: 8px; padding: 16px 18px; }
        .total-label { font-size: 11px; font-weight: 600; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.5px; }
        .total-amount { font-size: 24px; font-weight: 700; letter-spacing: -0.5px; margin-top: 4px; }

        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }

        .notes-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 14px; margin-top: 20px; }
        .notes-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; margin-bottom: 5px; }
        .notes-text { font-size: 12px; color: #374151; line-height: 1.5; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="brand">Skolet</div>
            <div class="brand-sub">School Management Platform</div>
        </div>
        <div class="header-right">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number">#{{ strtoupper(substr($payment->id, 0, 8)) }}</div>
        </div>
    </div>

    {{-- Meta --}}
    <div class="meta-grid">
        <div class="meta-col">
            <div class="meta-label">Billed To</div>
            <div class="meta-value">{{ $tenant->name }}</div>
            <div class="meta-value-muted">{{ $tenant->subdomain }}.skolet.com</div>
        </div>
        <div class="meta-col" style="text-align: right">
            <div class="meta-label">Invoice Date</div>
            <div class="meta-value">{{ $payment->created_at->format('d M Y') }}</div>
            @if ($payment->payment_reference)
                <div style="margin-top: 12px">
                    <div class="meta-label">Payment Reference</div>
                    <div class="meta-value" style="font-family: Courier New, monospace; font-size: 12px;">{{ $payment->payment_reference }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="divider"></div>

    {{-- Line items --}}
    <table class="line-items">
        <thead>
            <tr>
                <th>Description</th>
                <th>Cycle Period</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Platform Subscription Fee</strong><br>
                    <span style="font-size: 11px; color: #6b7280;">Skolet School Management Platform</span>
                </td>
                <td>
                    {{ $payment->cycle_start->format('d M Y') }} – {{ $payment->cycle_end->format('d M Y') }}
                </td>
                <td class="right">
                    <strong>GHS {{ number_format((float) $payment->amount, 2) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Total --}}
    <div class="total-row">
        <div class="total-spacer"></div>
        <div class="total-box">
            <div class="total-inner">
                <div class="total-label">Total Amount Paid</div>
                <div class="total-amount">GHS {{ number_format((float) $payment->amount, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if ($payment->notes)
        <div class="notes-box">
            <div class="notes-label">Notes</div>
            <div class="notes-text">{{ $payment->notes }}</div>
        </div>
    @endif

    {{-- Meta info --}}
    <div class="divider"></div>
    <div style="display: table; width: 100%; font-size: 11px; color: #6b7280;">
        <div style="display: table-cell;">
            Recorded by {{ $payment->recordedBy?->name ?? 'Super Admin' }}
        </div>
        <div style="display: table-cell; text-align: right;">
            Paid on {{ $payment->created_at->format('d M Y H:i') }}
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Skolet Platform · Payment receipt generated {{ now()->format('d M Y') }} · For support contact hello@skolet.com
    </div>

</div>
</body>
</html>

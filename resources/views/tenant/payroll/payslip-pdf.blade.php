<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip &mdash; {{ $item->staff->full_name }} &mdash; {{ $run->period_label }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #ffffff; }

        .page { padding: 32px 40px; }

        /* Header */
        .header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; margin-bottom: 20px; }
        .school-name { font-size: 20px; font-weight: 700; color: #111827; letter-spacing: -0.3px; }
        .payslip-title { font-size: 13px; font-weight: 600; color: #6b7280; margin-top: 4px; text-transform: uppercase; letter-spacing: 1px; }
        .period-label { font-size: 12px; color: #9ca3af; margin-top: 2px; }

        /* Staff info */
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-grid-row { display: table-row; }
        .info-label { display: table-cell; width: 140px; padding: 4px 0; font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { display: table-cell; padding: 4px 0; font-size: 12px; color: #111827; }

        /* Section heading */
        .section-heading { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6b7280; padding: 6px 10px; background: #f3f4f6; border-radius: 4px; margin-bottom: 0; }

        /* Earnings / Deductions table */
        .breakdown-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .breakdown-table th { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; padding: 6px 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .breakdown-table th.amount { text-align: right; }
        .breakdown-table td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; font-size: 12px; color: #374151; }
        .breakdown-table td.amount { text-align: right; }
        .breakdown-table tr.subtotal td { font-weight: 600; color: #111827; border-top: 1px solid #e5e7eb; background: #f9fafb; }

        /* Net pay box */
        .net-box { background: #1e3a5f; color: #ffffff; border-radius: 8px; padding: 18px 20px; display: table; width: 100%; margin-bottom: 20px; }
        .net-box-label { display: table-cell; font-size: 13px; font-weight: 600; vertical-align: middle; }
        .net-box-amount { display: table-cell; text-align: right; font-size: 22px; font-weight: 700; vertical-align: middle; letter-spacing: -0.5px; }

        /* Employer contributions block */
        .employer-block { border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 14px; background: #f9fafb; margin-bottom: 24px; }
        .employer-block-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #9ca3af; margin-bottom: 8px; }
        .employer-row { display: table; width: 100%; margin-bottom: 4px; }
        .employer-row-label { display: table-cell; font-size: 11px; color: #9ca3af; }
        .employer-row-amount { display: table-cell; text-align: right; font-size: 11px; color: #9ca3af; }

        /* Signature area */
        .signatures { display: table; width: 100%; margin-top: 24px; }
        .sig-cell { display: table-cell; width: 50%; padding-top: 32px; border-top: 1px solid #9ca3af; font-size: 11px; color: #6b7280; }
        .sig-cell.right { text-align: right; }

        /* Footer */
        .footer { text-align: center; font-size: 10px; color: #9ca3af; margin-top: 24px; padding-top: 12px; border-top: 1px solid #e5e7eb; }

        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }
        .text-green { color: #16a34a; }
        .text-red   { color: #dc2626; }
    </style>
</head>
<body>
<div class="page">

    {{-- School Header --}}
    <div class="header">
        <div class="school-name">{{ $school?->name ?? 'School' }}</div>
        <div class="payslip-title">Employee Payslip</div>
        <div class="period-label">{{ $run->period_label }}</div>
    </div>

    {{-- Staff Info --}}
    <div class="info-grid">
        <div class="info-grid-row">
            <div class="info-label">Employee Name</div>
            <div class="info-value">{{ $item->staff->full_name }}</div>
        </div>
        <div class="info-grid-row">
            <div class="info-label">Role / Title</div>
            <div class="info-value">{{ $item->staff->role_title ?? '&mdash;' }}</div>
        </div>
        <div class="info-grid-row">
            <div class="info-label">Pay Period</div>
            <div class="info-value">{{ $run->period_label }}</div>
        </div>
        <div class="info-grid-row">
            <div class="info-label">Processed</div>
            <div class="info-value">{{ $run->processed_at?->format('d M Y') ?? '&mdash;' }}</div>
        </div>
        <div class="info-grid-row">
            <div class="info-label">Payment Status</div>
            <div class="info-value">
                {{ ucfirst($item->payment_status) }}
                @if($item->paid_at)
                    &mdash; {{ $item->paid_at->format('d M Y') }}
                    ({{ match($item->payment_method) {
                        'bank_transfer' => 'Bank Transfer',
                        'mobile_money'  => 'Mobile Money',
                        'cash'          => 'Cash',
                        default         => ucfirst($item->payment_method ?? ''),
                    } }})
                @endif
            </div>
        </div>
    </div>

    <hr class="divider">

    {{-- Earnings --}}
    <div class="section-heading">Earnings</div>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Component</th>
                <th class="amount">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Gross Salary</td>
                <td class="amount">{{ number_format((float) $item->gross, 2) }}</td>
            </tr>
            @if($structure)
                @foreach($structure->allowances ?? [] as $key => $val)
                @if((float) $val > 0)
                <tr>
                    <td class="text-green">{{ ucfirst($key) }} Allowance</td>
                    <td class="amount text-green">+ {{ number_format((float) $val, 2) }}</td>
                </tr>
                @endif
                @endforeach
            @elseif((float) $item->allowances_total > 0)
            <tr>
                <td class="text-green">Total Allowances</td>
                <td class="amount text-green">+ {{ number_format((float) $item->allowances_total, 2) }}</td>
            </tr>
            @endif
            <tr class="subtotal">
                <td>Total Earnings</td>
                <td class="amount">{{ number_format((float) $item->gross + (float) $item->allowances_total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Statutory Deductions (Ghana Tax) --}}
    <div class="section-heading">Statutory Deductions</div>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Component</th>
                <th class="amount">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-red">SSNIT &mdash; Employee Contribution (5.5%)</td>
                <td class="amount text-red">− {{ number_format((float) $item->ssnit_employee, 2) }}</td>
            </tr>
            <tr>
                <td class="text-red">Tier 2 &mdash; Employee Contribution (5%)</td>
                <td class="amount text-red">− {{ number_format((float) $item->tier2_employee, 2) }}</td>
            </tr>
            <tr>
                <td class="text-red">PAYE &mdash; Income Tax (GRA 2024)</td>
                <td class="amount text-red">− {{ number_format((float) $item->paye, 2) }}</td>
            </tr>
            <tr class="subtotal">
                <td>Total Statutory Deductions</td>
                <td class="amount text-red">
                    − {{ number_format((float) $item->ssnit_employee + (float) $item->tier2_employee + (float) $item->paye, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Other / Manual Deductions --}}
    @php $manualDeductionsTotal = (float) $item->deductions_total; @endphp
    @if($manualDeductionsTotal > 0)
    <div class="section-heading">Other Deductions</div>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Component</th>
                <th class="amount">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @if($structure)
                @foreach($structure->deductions ?? [] as $key => $val)
                @if((float) $val > 0)
                <tr>
                    <td class="text-red">{{ ucfirst($key) }}</td>
                    <td class="amount text-red">− {{ number_format((float) $val, 2) }}</td>
                </tr>
                @endif
                @endforeach
            @else
            <tr>
                <td class="text-red">Total Other Deductions</td>
                <td class="amount text-red">− {{ number_format($manualDeductionsTotal, 2) }}</td>
            </tr>
            @endif
            <tr class="subtotal">
                <td>Total Other Deductions</td>
                <td class="amount text-red">− {{ number_format($manualDeductionsTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    {{-- Net Pay --}}
    <div class="net-box">
        <div class="net-box-label">NET PAY</div>
        <div class="net-box-amount">GHS {{ number_format((float) $item->net, 2) }}</div>
    </div>

    {{-- Employer Contributions (informational) --}}
    <div class="employer-block">
        <div class="employer-block-title">Employer Contributions (School Liability &mdash; not deducted from employee)</div>
        <div class="employer-row">
            <div class="employer-row-label">SSNIT &mdash; Employer Contribution (13%)</div>
            <div class="employer-row-amount">GHS {{ number_format((float) $item->ssnit_employer, 2) }}</div>
        </div>
        <div class="employer-row">
            <div class="employer-row-label">Tier 2 &mdash; Employer Contribution (5%)</div>
            <div class="employer-row-amount">GHS {{ number_format((float) $item->tier2_employer, 2) }}</div>
        </div>
        <div class="employer-row">
            <div class="employer-row-label" style="font-weight:600; color:#6b7280;">Total Employer Liability</div>
            <div class="employer-row-amount" style="font-weight:600; color:#6b7280;">
                GHS {{ number_format((float) $item->ssnit_employer + (float) $item->tier2_employer, 2) }}
            </div>
        </div>
    </div>

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig-cell">Employee Signature</div>
        <div class="sig-cell right">Authorised By</div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        This is a computer-generated payslip. Generated on {{ now()->format('d M Y') }} by {{ $school?->name ?? 'SchoolFlow' }}.
    </div>

</div>
</body>
</html>

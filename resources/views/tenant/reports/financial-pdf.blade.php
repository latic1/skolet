<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Summary &mdash; {{ $report['term'] ? $report['term']->name . ' ' . $report['academic_year']->name : $report['academic_year']->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1f2937; background: #fff; }
        .page { padding: 28px 36px; }

        /* Header */
        .header { display: table; width: 100%; padding-bottom: 16px; border-bottom: 2px solid #e5e7eb; margin-bottom: 18px; }
        .header-left  { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; text-align: right; vertical-align: middle; }
        .school-name  { font-size: 17px; font-weight: 700; color: #111827; }
        .report-title { font-size: 12px; font-weight: 600; color: #6b7280; margin-top: 2px; }
        .period-label { font-size: 10px; color: #9ca3af; margin-top: 2px; }

        /* Summary cards */
        .summary-row { display: table; width: 100%; margin-bottom: 20px; border-collapse: separate; border-spacing: 8px 0; }
        .summary-card { display: table-cell; width: 33.33%; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 8px; }
        .summary-label { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; color: #6b7280; margin-bottom: 4px; }
        .summary-value { font-size: 18px; font-weight: 700; }
        .summary-sub   { font-size: 9px; color: #9ca3af; margin-top: 2px; }
        .income-value  { color: #16a34a; }
        .expense-value { color: #dc2626; }
        .net-positive  { color: #16a34a; }
        .net-negative  { color: #dc2626; }

        /* Section heading */
        .section-heading { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6b7280; padding: 5px 8px; background: #f3f4f6; border-radius: 4px; margin-bottom: 0; }

        /* Two-col layout */
        .two-col { display: table; width: 100%; border-spacing: 10px 0; border-collapse: separate; margin-top: 16px; }
        .col-half { display: table-cell; width: 50%; vertical-align: top; }

        /* Tables */
        .breakdown-table { width: 100%; border-collapse: collapse; }
        .breakdown-table th { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; padding: 5px 8px; text-align: left; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        .breakdown-table th.right { text-align: right; }
        .breakdown-table td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; font-size: 10px; color: #374151; }
        .breakdown-table td.right { text-align: right; }
        .breakdown-table tfoot td { font-weight: 600; font-size: 10px; border-top: 1px solid #e5e7eb; background: #f9fafb; padding: 5px 8px; }
        .breakdown-table tfoot td.right { text-align: right; }
        .text-green { color: #16a34a; }
        .text-red   { color: #dc2626; }

        /* Monthly trend table */
        .trend-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .trend-table th { font-size: 9px; font-weight: 600; text-transform: uppercase; color: #9ca3af; padding: 5px 8px; text-align: left; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        .trend-table th.right { text-align: right; }
        .trend-table td { padding: 4px 8px; border-bottom: 1px solid #f3f4f6; font-size: 10px; color: #374151; }
        .trend-table td.right { text-align: right; }
        .trend-table tr.has-data { background: #fafafa; }

        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 14px 0; }
        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="school-name">{{ $profile?->name ?? 'School' }}</div>
            <div class="report-title">Financial Summary Report</div>
            <div class="period-label">
                @if($report['term'])
                    {{ $report['term']->name }} &mdash; {{ $report['academic_year']->name }}
                @else
                    {{ $report['academic_year']->name }} (Full Year)
                @endif
                &nbsp;&middot;&nbsp;
                {{ $report['date_from']->format('d M Y') }} &ndash; {{ $report['date_to']->format('d M Y') }}
            </div>
        </div>
        <div class="header-right">
            <div class="period-label">Generated {{ now()->format('d M Y') }}</div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-row">
        <div class="summary-card">
            <div class="summary-label">Total Income</div>
            <div class="summary-value income-value">{{ number_format($report['income_total'], 2) }}</div>
            <div class="summary-sub">Fee collections received</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Expenses</div>
            <div class="summary-value expense-value">{{ number_format($report['expense_total'], 2) }}</div>
            <div class="summary-sub">Recorded expenditure</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Net Balance</div>
            <div class="summary-value {{ $report['net'] >= 0 ? 'net-positive' : 'net-negative' }}">
                {{ $report['net'] < 0 ? '(' : '' }}{{ number_format(abs($report['net']), 2) }}{{ $report['net'] < 0 ? ')' : '' }}
            </div>
            <div class="summary-sub">{{ $report['net'] >= 0 ? 'Surplus' : 'Deficit' }}</div>
        </div>
    </div>

    {{-- Breakdown tables side by side --}}
    <div class="two-col">
        {{-- Income breakdown --}}
        <div class="col-half">
            <div class="section-heading">Income by Fee Item</div>
            @if(empty($report['income_by_category']))
            <p style="padding:12px 8px; font-size:10px; color:#9ca3af;">No fee payments in this period.</p>
            @else
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Fee Item</th>
                        <th class="right">Collected</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['income_by_category'] as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td class="right text-green">{{ number_format($row['amount'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total Income</td>
                        <td class="right text-green">{{ number_format($report['income_total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @endif
        </div>

        {{-- Expense breakdown --}}
        <div class="col-half">
            <div class="section-heading">Expenses by Category</div>
            @if(empty($report['expense_by_category']))
            <p style="padding:12px 8px; font-size:10px; color:#9ca3af;">No expenses recorded in this period.</p>
            @else
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="right">Spent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['expense_by_category'] as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td class="right text-red">{{ number_format($row['amount'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total Expenses</td>
                        <td class="right text-red">{{ number_format($report['expense_total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @endif
        </div>
    </div>

    {{-- Monthly Trend Table --}}
    @if(count($report['monthly_trend']) > 0)
    <hr class="divider">
    <div class="section-heading">Monthly Trend</div>
    <table class="trend-table">
        <thead>
            <tr>
                <th>Month</th>
                <th class="right">Income</th>
                <th class="right">Expenses</th>
                <th class="right">Net</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['monthly_trend'] as $row)
            @php $net = $row['income'] - $row['expenses']; @endphp
            <tr class="{{ ($row['income'] > 0 || $row['expenses'] > 0) ? 'has-data' : '' }}">
                <td>{{ $row['month'] }}</td>
                <td class="right text-green">{{ $row['income'] > 0 ? number_format($row['income'], 2) : '—' }}</td>
                <td class="right text-red">{{ $row['expenses'] > 0 ? number_format($row['expenses'], 2) : '—' }}</td>
                <td class="right {{ $net >= 0 ? 'text-green' : 'text-red' }}">
                    {{ ($row['income'] > 0 || $row['expenses'] > 0) ? number_format($net, 2) : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        {{ $profile?->name ?? 'SchoolFlow' }} &middot; Financial Summary &middot; Generated {{ now()->format('d M Y H:i') }}
    </div>

</div>
</body>
</html>

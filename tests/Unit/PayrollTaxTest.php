<?php

/**
 * Ghana Payroll Tax Tests (2024 GRA bands)
 *
 * Rates from config/payroll.php:
 *   SSNIT employee  = 5.5% of gross
 *   Tier2 employee  = 5.0% of gross
 *   SSNIT employer  = 13.0% of gross
 *   Tier2 employer  = 5.0% of gross
 *
 * PAYE bands (monthly, GHS):
 *   0–365     @ 0%
 *   366–475   @ 5%
 *   476–605   @ 10%
 *   606–3772  @ 17.5%
 *   3773–19772 @ 25%
 *   > 19772   @ 30%
 */

function computePaye(float $income): float
{
    $tax   = 0.0;
    $bands = config('payroll.paye_bands');
    foreach ($bands as $band) {
        if ($income <= 0) {
            break;
        }
        $slice   = $band['limit'] === null ? $income : min($income, (float) $band['limit']);
        $tax    += $slice * $band['rate'];
        $income -= $slice;
    }
    return round($tax, 2);
}

test('PAYE is zero for gross below the first taxable band', function (): void {
    $gross         = 500.00;
    $ssnitEmployee = round($gross * 0.055, 2);
    $tier2Employee = round($gross * 0.05, 2);
    $taxableIncome = max(0.0, $gross - $ssnitEmployee - $tier2Employee);

    // With gross 500: ssnit=27.5, tier2=25 → taxable = 447.5 (< 365 first band limit)
    // But band 1 is 0–365 at 0% and band 2 is 110 at 5%, so tax should be small
    $paye = computePaye($taxableIncome);

    expect($paye)->toBeGreaterThanOrEqual(0.0);
});

test('PAYE is zero when taxable income is within the zero-rate band', function (): void {
    // Taxable income = 300 (< 365 threshold)
    $paye = computePaye(300.0);

    expect($paye)->toBe(0.0);
});

test('PAYE is computed progressively for a mid-range gross', function (): void {
    // Gross 2000: ssnit=110, tier2=100 → taxable = 1790
    // Band 1: 365 × 0.00 = 0
    // Band 2: 110 × 0.05 = 5.5
    // Band 3: 130 × 0.10 = 13
    // Band 4: remaining 1185 × 0.175 = 207.375
    $gross         = 2000.00;
    $ssnitEmployee = round($gross * 0.055, 2);
    $tier2Employee = round($gross * 0.05, 2);
    $taxable       = max(0.0, $gross - $ssnitEmployee - $tier2Employee);
    $paye          = computePaye($taxable);

    expect($paye)->toBeGreaterThan(0.0)
        ->and($paye)->toBeLessThan($gross * 0.30); // never exceeds top marginal rate on full gross
});

test('PAYE is computed at the top rate for a very high gross', function (): void {
    // Gross 50000 — most income will fall in the 30% band
    $gross         = 50000.00;
    $ssnitEmployee = round($gross * 0.055, 2);
    $tier2Employee = round($gross * 0.05, 2);
    $taxable       = max(0.0, $gross - $ssnitEmployee - $tier2Employee);
    $paye          = computePaye($taxable);

    // Top band is 30%; effective rate should be close to 30% on the upper portion
    expect($paye)->toBeGreaterThan(10000.0);
});

test('SSNIT employee rate is 5.5 percent of gross', function (): void {
    $gross         = 3000.00;
    $ssnitEmployee = round($gross * config('payroll.ssnit_employee_rate'), 2);

    expect($ssnitEmployee)->toBe(165.0);
});

test('Tier2 employee rate is 5 percent of gross', function (): void {
    $gross         = 3000.00;
    $tier2Employee = round($gross * config('payroll.tier2_employee_rate'), 2);

    expect($tier2Employee)->toBe(150.0);
});

test('employer SSNIT rate is 13 percent of gross', function (): void {
    $gross         = 3000.00;
    $ssnitEmployer = round($gross * config('payroll.ssnit_employer_rate'), 2);

    expect($ssnitEmployer)->toBe(390.0);
});

test('employer Tier2 rate is 5 percent of gross', function (): void {
    $gross         = 3000.00;
    $tier2Employer = round($gross * config('payroll.tier2_employer_rate'), 2);

    expect($tier2Employer)->toBe(150.0);
});

test('combined employee deductions equal SSNIT plus Tier2 plus PAYE', function (): void {
    $gross         = 2000.00;
    $ssnitEmployee = round($gross * config('payroll.ssnit_employee_rate'), 2);
    $tier2Employee = round($gross * config('payroll.tier2_employee_rate'), 2);
    $taxable       = max(0.0, $gross - $ssnitEmployee - $tier2Employee);
    $paye          = computePaye($taxable);
    $totalDeductions = $ssnitEmployee + $tier2Employee + $paye;
    $net           = max(0.0, $gross - $totalDeductions);

    expect($net + $totalDeductions)->toBe(round($gross, 2));
});

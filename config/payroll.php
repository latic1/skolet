<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Ghana Statutory Payroll Rates (2024)
    |--------------------------------------------------------------------------
    */

    // Employee SSNIT Tier 1 contribution
    'ssnit_employee_rate' => 0.055,

    // Employee mandatory occupational pension (Tier 2)
    'tier2_employee_rate' => 0.05,

    // Employer SSNIT Tier 1 contribution
    'ssnit_employer_rate' => 0.13,

    // Employer mandatory occupational pension (Tier 2)
    'tier2_employer_rate' => 0.05,

    /*
    |--------------------------------------------------------------------------
    | PAYE Monthly Bands — 2024 GRA
    |--------------------------------------------------------------------------
    | Each band: limit (GHS, null = remaining), rate (fraction).
    | Applied progressively to taxable income after SSNIT & Tier 2 deductions.
    |--------------------------------------------------------------------------
    */
    'paye_bands' => [
        ['limit' => 365,   'rate' => 0.00],
        ['limit' => 110,   'rate' => 0.05],
        ['limit' => 130,   'rate' => 0.10],
        ['limit' => 3167,  'rate' => 0.175],
        ['limit' => 16000, 'rate' => 0.25],
        ['limit' => null,  'rate' => 0.30],
    ],

];

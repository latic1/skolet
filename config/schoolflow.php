<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Grading Scale
    |--------------------------------------------------------------------------
    |
    | Applied by ReportCardService to convert a numeric marks (out of 100)
    | into a letter grade and remark. Entries are evaluated top-to-bottom;
    | the first entry whose min <= marks <= max wins.
    |
    */
    /*
    |--------------------------------------------------------------------------
    | Default Rate Per Student
    |--------------------------------------------------------------------------
    |
    | The standard annual subscription rate (in GHS) charged per student.
    | Applied when a new tenant is provisioned. Super Admin can override
    | this per-school via the Super Admin dashboard (Feature 21).
    |
    */
    'default_rate_per_student' => env('DEFAULT_RATE_PER_STUDENT', 5.00),

    'default_grading_scale' => [
        ['min' => 70, 'max' => 100, 'grade' => 'A', 'remark' => 'Excellent'],
        ['min' => 60, 'max' => 69,  'grade' => 'B', 'remark' => 'Very Good'],
        ['min' => 50, 'max' => 59,  'grade' => 'C', 'remark' => 'Good'],
        ['min' => 40, 'max' => 49,  'grade' => 'D', 'remark' => 'Pass'],
        ['min' => 0,  'max' => 39,  'grade' => 'F', 'remark' => 'Fail'],
    ],

];

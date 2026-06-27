<?php

use App\Models\Tenant\ExamResult;
use App\Models\Tenant\SchoolProfile;
use App\Services\ReportCardService;

/**
 * Grading Scale tests.
 *
 * Default scale from config/skolet.php:
 *   A  70–100  Excellent
 *   B  60–69   Very Good
 *   C  50–59   Good
 *   D  40–49   Pass
 *   F  0–39    Fail
 */

test('default grading scale grades 75 as A', function (): void {
    $scale = config('skolet.default_grading_scale');
    expect(ExamResult::computeGrade(75.0, $scale))->toBe('A');
});

test('default grading scale grades 65 as B', function (): void {
    $scale = config('skolet.default_grading_scale');
    expect(ExamResult::computeGrade(65.0, $scale))->toBe('B');
});

test('default grading scale grades 55 as C', function (): void {
    $scale = config('skolet.default_grading_scale');
    expect(ExamResult::computeGrade(55.0, $scale))->toBe('C');
});

test('default grading scale grades 45 as D', function (): void {
    $scale = config('skolet.default_grading_scale');
    expect(ExamResult::computeGrade(45.0, $scale))->toBe('D');
});

test('default grading scale grades 30 as F', function (): void {
    $scale = config('skolet.default_grading_scale');
    expect(ExamResult::computeGrade(30.0, $scale))->toBe('F');
});

test('custom grading scale overrides default in ExamResult::computeGrade', function (): void {
    // Custom scale: A≥80, B≥70, C≥60, D≥50, F<50
    $customScale = [
        ['min' => 80, 'max' => 100, 'grade' => 'A', 'remark' => 'Distinction'],
        ['min' => 70, 'max' => 79,  'grade' => 'B', 'remark' => 'Credit'],
        ['min' => 60, 'max' => 69,  'grade' => 'C', 'remark' => 'Merit'],
        ['min' => 50, 'max' => 59,  'grade' => 'D', 'remark' => 'Pass'],
        ['min' => 0,  'max' => 49,  'grade' => 'F', 'remark' => 'Fail'],
    ];

    // Under the default scale, 75 = A. Under the custom scale, 75 = B.
    expect(ExamResult::computeGrade(75.0, $customScale))->toBe('B');
});

test('custom grading scale with higher threshold changes pass boundary', function (): void {
    $customScale = [
        ['min' => 80, 'max' => 100, 'grade' => 'A', 'remark' => 'Distinction'],
        ['min' => 60, 'max' => 79,  'grade' => 'B', 'remark' => 'Pass'],
        ['min' => 0,  'max' => 59,  'grade' => 'F', 'remark' => 'Fail'],
    ];

    expect(ExamResult::computeGrade(65.0, $customScale))->toBe('B')
        ->and(ExamResult::computeGrade(55.0, $customScale))->toBe('F');
});

test('grading scale validation: overlapping bands should be detected', function (): void {
    // Simulate the validation logic from StoreGradingScaleRequest
    $overlappingScale = [
        ['min' => 70, 'max' => 100, 'grade' => 'A', 'remark' => 'Excellent'],
        ['min' => 60, 'max' => 75,  'grade' => 'B', 'remark' => 'Very Good'], // overlaps with A
        ['min' => 0,  'max' => 59,  'grade' => 'F', 'remark' => 'Fail'],
    ];

    $hasOverlap = false;
    $sorted     = collect($overlappingScale)->sortBy('min')->values();
    for ($i = 1; $i < $sorted->count(); $i++) {
        if ($sorted[$i]['min'] <= $sorted[$i - 1]['max']) {
            $hasOverlap = true;
            break;
        }
    }

    expect($hasOverlap)->toBeTrue();
});

test('grading scale validation: scale not covering 0 to 100 should be detected', function (): void {
    $incompleteScale = [
        ['min' => 70, 'max' => 100, 'grade' => 'A', 'remark' => 'Excellent'],
        ['min' => 50, 'max' => 69,  'grade' => 'B', 'remark' => 'Good'],
        // Gap: 0–49 not covered
    ];

    $sorted  = collect($incompleteScale)->sortBy('min')->values();
    $missing = $sorted->first()['min'] > 0 || $sorted->last()['max'] < 100;

    expect($missing)->toBeTrue();
});

test('valid grading scale covering 0 to 100 with no overlaps passes validation', function (): void {
    $validScale = [
        ['min' => 70, 'max' => 100, 'grade' => 'A', 'remark' => 'Excellent'],
        ['min' => 50, 'max' => 69,  'grade' => 'B', 'remark' => 'Good'],
        ['min' => 0,  'max' => 49,  'grade' => 'F', 'remark' => 'Fail'],
    ];

    $sorted     = collect($validScale)->sortBy('min')->values();
    $hasOverlap = false;
    for ($i = 1; $i < $sorted->count(); $i++) {
        if ($sorted[$i]['min'] <= $sorted[$i - 1]['max']) {
            $hasOverlap = true;
            break;
        }
    }

    $fullCoverage = $sorted->first()['min'] === 0 && $sorted->last()['max'] === 100;

    expect($hasOverlap)->toBeFalse()
        ->and($fullCoverage)->toBeTrue();
});

<?php

use App\Services\ReportCardService;

beforeEach(function (): void {
    $this->service = new ReportCardService();
    $this->scale   = config('skolet.default_grading_scale');

    // Access private applyScale via reflection
    $ref = new ReflectionClass($this->service);
    $this->applyScale = $ref->getMethod('applyScale');
    $this->applyScale->setAccessible(true);

    $this->barColor = $ref->getMethod('barColor');
    $this->barColor->setAccessible(true);
});

// Helper to call applyScale(marks, scale)
function applyScale(mixed $service, ReflectionMethod $method, float $marks, array $scale): array
{
    return $method->invoke($service, $marks, $scale);
}

test('marks of 70 or above return grade A', function (): void {
    foreach ([70, 85, 100] as $marks) {
        [$grade] = applyScale($this->service, $this->applyScale, (float) $marks, $this->scale);
        expect($grade)->toBe('A');
    }
});

test('marks between 60 and 69 return grade B', function (): void {
    foreach ([60, 65, 69] as $marks) {
        [$grade] = applyScale($this->service, $this->applyScale, (float) $marks, $this->scale);
        expect($grade)->toBe('B');
    }
});

test('marks between 50 and 59 return grade C', function (): void {
    foreach ([50, 55, 59] as $marks) {
        [$grade] = applyScale($this->service, $this->applyScale, (float) $marks, $this->scale);
        expect($grade)->toBe('C');
    }
});

test('marks between 40 and 49 return grade D', function (): void {
    foreach ([40, 45, 49] as $marks) {
        [$grade] = applyScale($this->service, $this->applyScale, (float) $marks, $this->scale);
        expect($grade)->toBe('D');
    }
});

test('marks below 40 return grade F', function (): void {
    foreach ([0, 20, 39] as $marks) {
        [$grade] = applyScale($this->service, $this->applyScale, (float) $marks, $this->scale);
        expect($grade)->toBe('F');
    }
});

test('grade A returns correct remark', function (): void {
    [, $remark] = applyScale($this->service, $this->applyScale, 80.0, $this->scale);
    expect($remark)->toBe('Excellent');
});

test('grade F returns correct remark', function (): void {
    [, $remark] = applyScale($this->service, $this->applyScale, 10.0, $this->scale);
    expect($remark)->toBe('Fail');
});

test('barColor returns green for grade A', function (): void {
    $color = $this->barColor->invoke($this->service, 'A');
    expect($color)->toBe('#10b981');
});

test('barColor returns red for grade F', function (): void {
    $color = $this->barColor->invoke($this->service, 'F');
    expect($color)->toBe('#ef4444');
});

test('default grading scale covers 0 to 100 continuously', function (): void {
    $covered = [];
    foreach ($this->scale as $band) {
        for ($i = $band['min']; $i <= $band['max']; $i++) {
            $covered[] = $i;
        }
    }
    expect(min($covered))->toBe(0);
    expect(max($covered))->toBe(100);
    expect(count($covered))->toBe(101);
});

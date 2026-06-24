<?php

use App\Services\FeeStatusService;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    $this->service = new FeeStatusService();
});

test('computeStatus returns paid when paid amount equals total', function (): void {
    expect($this->service->computeStatus(100.0, 100.0, null))->toBe('paid');
});

test('computeStatus returns paid when paid amount exceeds total', function (): void {
    expect($this->service->computeStatus(100.0, 120.0, null))->toBe('paid');
});

test('computeStatus returns unpaid when nothing paid and not overdue', function (): void {
    $future = Carbon::tomorrow();
    expect($this->service->computeStatus(100.0, 0.0, $future))->toBe('unpaid');
});

test('computeStatus returns unpaid when nothing paid and no due date', function (): void {
    expect($this->service->computeStatus(100.0, 0.0, null))->toBe('unpaid');
});

test('computeStatus returns partial when partially paid and not overdue', function (): void {
    $future = Carbon::tomorrow();
    expect($this->service->computeStatus(100.0, 50.0, $future))->toBe('partial');
});

test('computeStatus returns overdue when nothing paid and due date is past', function (): void {
    $past = Carbon::yesterday();
    expect($this->service->computeStatus(100.0, 0.0, $past))->toBe('overdue');
});

test('computeStatus returns overdue when partially paid and due date is past', function (): void {
    $past = Carbon::yesterday();
    expect($this->service->computeStatus(100.0, 50.0, $past))->toBe('overdue');
});

test('computeStatus returns partial when partially paid and no due date', function (): void {
    expect($this->service->computeStatus(100.0, 50.0, null))->toBe('partial');
});

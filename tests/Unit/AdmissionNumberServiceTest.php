<?php

use App\Services\AdmissionNumberService;

beforeEach(function (): void {
    $this->service = new AdmissionNumberService();
});

test('applyPattern substitutes YEAR placeholder', function (): void {
    $result = $this->service->preview('{YEAR}/0001', 1);
    expect($result)->toBe(date('Y') . '/0001');
});

test('applyPattern substitutes YY placeholder', function (): void {
    $result = $this->service->preview('{YY}/0001', 1);
    expect($result)->toBe(date('y') . '/0001');
});

test('applyPattern zero-pads SEQ to 4 digits by default', function (): void {
    $result = $this->service->preview('{YEAR}/{SEQ}', 7);
    expect($result)->toBe(date('Y') . '/0007');
});

test('applyPattern respects custom SEQ padding', function (): void {
    $result = $this->service->preview('{YEAR}/{SEQ:6}', 42);
    expect($result)->toBe(date('Y') . '/000042');
});

test('applyPattern handles counter at 1000', function (): void {
    $result = $this->service->preview('{YEAR}/{SEQ:4}', 1000);
    expect($result)->toBe(date('Y') . '/1000');
});

test('preview returns formatted string without DB interaction', function (): void {
    $result = $this->service->preview('{YEAR}/{SEQ:4}', 99);
    expect($result)->toBeString()->toContain('/');
});

test('default pattern constant follows expected format', function (): void {
    expect(AdmissionNumberService::DEFAULT_PATTERN)->toBe('{YEAR}/{SEQ:4}');
});

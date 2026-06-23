<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use Illuminate\Support\Facades\DB;

final class AdmissionNumberService
{
    public const DEFAULT_PATTERN = '{YEAR}/{SEQ:4}';

    /**
     * Atomically increment the counter and return the next admission number.
     */
    public function generate(): string
    {
        $counter = 1;
        $pattern = self::DEFAULT_PATTERN;

        DB::transaction(function () use (&$counter, &$pattern): void {
            $profile = SchoolProfile::lockForUpdate()->first();

            if ($profile) {
                $profile->increment('admission_counter');
                $counter = $profile->admission_counter;
                $pattern = $profile->admission_pattern ?: self::DEFAULT_PATTERN;
            } else {
                // No profile yet — fall back to current student count + 1
                $counter = Student::count() + 1;
            }
        });

        return $this->applyPattern($pattern, $counter);
    }

    /**
     * Render a pattern with a given counter value — used for live previews.
     */
    public function preview(string $pattern, int $counter = 1): string
    {
        return $this->applyPattern($pattern, $counter);
    }

    /**
     * Reset the sequence counter back to zero.
     */
    public function resetCounter(): void
    {
        SchoolProfile::query()->update(['admission_counter' => 0]);
    }

    private function applyPattern(string $pattern, int $counter): string
    {
        $now = now();

        $pattern = str_replace('{YEAR}', (string) $now->year, $pattern);
        $pattern = str_replace('{YY}', $now->format('y'), $pattern);

        $pattern = (string) preg_replace_callback(
            '/\{SEQ(?::(\d+))?\}/',
            static function (array $m) use ($counter): string {
                $pad = (isset($m[1]) && $m[1] !== '') ? (int) $m[1] : 4;
                return str_pad((string) $counter, $pad, '0', STR_PAD_LEFT);
            },
            $pattern
        );

        return $pattern;
    }
}

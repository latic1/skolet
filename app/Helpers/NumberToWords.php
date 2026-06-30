<?php

declare(strict_types=1);

namespace App\Helpers;

final class NumberToWords
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    public static function convert(float $amount, string $currency = 'Cedis', string $subunit = 'Pesewas'): string
    {
        $intPart = (int) floor(abs($amount));
        $decPart = (int) round((abs($amount) - $intPart) * 100);

        $words = self::intToWords($intPart) . ' ' . $currency;

        if ($decPart > 0) {
            $words .= ', ' . self::intToWords($decPart) . ' ' . $subunit;
        } else {
            $words .= ', Zero ' . $subunit;
        }

        return $words;
    }

    private static function intToWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        if ($number < 0) {
            return 'Negative ' . self::intToWords(-$number);
        }

        if ($number < 20) {
            return self::ONES[$number];
        }

        if ($number < 100) {
            $tens = self::TENS[intdiv($number, 10)];
            $ones = $number % 10 !== 0 ? ' ' . self::ONES[$number % 10] : '';
            return $tens . $ones;
        }

        if ($number < 1000) {
            $hundreds = self::ONES[intdiv($number, 100)] . ' Hundred';
            $remainder = $number % 100;
            return $remainder !== 0 ? $hundreds . ' ' . self::intToWords($remainder) : $hundreds;
        }

        if ($number < 1_000_000) {
            $thousands = self::intToWords(intdiv($number, 1000)) . ' Thousand';
            $remainder = $number % 1000;
            return $remainder !== 0 ? $thousands . ' ' . self::intToWords($remainder) : $thousands;
        }

        $millions  = self::intToWords(intdiv($number, 1_000_000)) . ' Million';
        $remainder = $number % 1_000_000;
        return $remainder !== 0 ? $millions . ' ' . self::intToWords($remainder) : $millions;
    }
}

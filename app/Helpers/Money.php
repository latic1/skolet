<?php

declare(strict_types=1);

if (! function_exists('format_money')) {
    function format_money(float $amount, string $symbol = '₵'): string
    {
        return $symbol . ' ' . number_format($amount, 2);
    }
}

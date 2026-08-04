<?php

namespace App\Services;

use Carbon\Carbon;
use JobMetric\MultiCalendar\Converters\HebrewConverter;

class HebrewDateService
{
    protected HebrewConverter $converter;

    public function __construct()
    {
        $this->converter = new HebrewConverter();
    }

    /**
     * Convert a Carbon date to Hebrew year/month/day array
     */
    public function toHebrewArray(Carbon $date): array
    {
        $result = $this->converter->fromGregorian(
            $date->year,
            $date->month,
            $date->day
        );

        if (is_string($result)) {
            // Parse the string result if needed
            return $this->parseHebrewString($result);
        }

        return $result;
    }

    /**
     * Convert a Carbon date to Hebrew string representation
     */
    public function toHebrewString(Carbon $date): string
    {
        $hebrew = $this->toHebrewArray($date);
        
        $year = $hebrew['year'] ?? $hebrew['y'] ?? '';
        $month = $hebrew['month'] ?? $hebrew['m'] ?? 1;
        $day = $hebrew['day'] ?? $hebrew['d'] ?? 1;

        $hebrewMonthName = $this->getHebrewMonthName($month);
        
        return "{$day} {$hebrewMonthName} {$year}";
    }

    /**
     * Get Hebrew month name by number
     * Handles leap year with Adar I / Adar II
     */
    protected function getHebrewMonthName(int $month): string
    {
        $months = [
            1 => 'ניסן',
            2 => 'אייר',
            3 => 'סיוון',
            4 => 'תמוז',
            5 => 'אב',
            6 => 'אלול',
            7 => 'תשרי',
            8 => 'חשוון',
            9 => 'כסלו',
            10 => 'טבת',
            11 => 'שבט',
            12 => 'אדר',
            13 => 'אדר א׳', // Adar I (leap year)
        ];

        // For leap years, month 14 would be Adar II
        if ($month === 14) {
            return 'אדר ב׳'; // Adar II
        }

        return $months[$month] ?? 'חודש לא ידוע';
    }

    /**
     * Parse Hebrew date string if converter returns string
     */
    protected function parseHebrewString(string $date): array
    {
        // This is a fallback - the converter should return an array
        // but we handle strings just in case
        $parts = explode(' ', $date);
        
        return [
            'year' => $parts[0] ?? '',
            'month' => $parts[1] ?? 1,
            'day' => $parts[2] ?? 1,
        ];
    }

    /**
     * Check if a given Hebrew year is a leap year
     */
    public function isLeapYear(int $hebrewYear): bool
    {
        // Hebrew leap years occur in years 3, 6, 8, 11, 14, 17, 19 of the 19-year cycle
        $yearInCycle = $hebrewYear % 19;
        return in_array($yearInCycle, [0, 3, 6, 8, 11, 14, 17]);
    }
}

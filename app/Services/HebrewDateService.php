<?php

namespace App\Services;

use Carbon\Carbon;
use JobMetric\MultiCalendar\Converters\HebrewConverter;

class HebrewDateService
{
    protected HebrewConverter $converter;

    public function __construct()
    {
        $this->converter = new HebrewConverter;
    }

    /**
     * Convert a Carbon date to an associative Hebrew year/month/day array
     *
     * @return array{year: int, month: int, day: int}
     */
    public function toHebrewArray(Carbon $date): array
    {
        $result = $this->converter->fromGregorian(
            $date->year,
            $date->month,
            $date->day
        );

        if (is_string($result)) {
            return $this->parseHebrewString($result);
        }

        return [
            'year' => $result[0],
            'month' => $result[1],
            'day' => $result[2],
        ];
    }

    /**
     * Convert a Carbon date to Hebrew string representation
     */
    public function toHebrewString(Carbon $date): string
    {
        $hebrew = $this->toHebrewArray($date);

        $isLeap = $this->isLeapYear($hebrew['year']);

        return "{$hebrew['day']} {$this->getHebrewMonthName($hebrew['month'], $isLeap)} {$hebrew['year']}";
    }

    /**
     * Convert a Carbon date to Hebrew day + month without the year
     */
    public function toHebrewDayMonthString(Carbon $date): string
    {
        $hebrew = $this->toHebrewArray($date);

        $isLeap = $this->isLeapYear($hebrew['year']);

        return "{$hebrew['day']} {$this->getHebrewMonthName($hebrew['month'], $isLeap)}";
    }

    /**
     * Get the Hebrew month name (without a year) for a Carbon date
     */
    public function hebrewMonthName(Carbon $date): string
    {
        $hebrew = $this->toHebrewArray($date);

        $isLeap = $this->isLeapYear($hebrew['year']);

        return $this->getHebrewMonthName($hebrew['month'], $isLeap);
    }

    /**
     * Get Hebrew month name by number.
     *
     * ICU (the underlying converter) numbers Hebrew months on a fixed 1-13
     * slot scheme where 1 = Tishrei and 8 = Nisan. In a non-leap year month 6
     * (Adar I) is simply skipped and plain Adar occupies slot 7; in a leap
     * year slot 6 is Adar I and slot 7 is Adar II.
     */
    protected function getHebrewMonthName(int $month, bool $isLeap): string
    {
        $months = [
            1 => 'תשרי',
            2 => 'חשוון',
            3 => 'כסלו',
            4 => 'טבת',
            5 => 'שבט',
            6 => 'אדר א׳', // Adar I, leap years only
            8 => 'ניסן',
            9 => 'אייר',
            10 => 'סיוון',
            11 => 'תמוז',
            12 => 'אב',
            13 => 'אלול',
        ];

        if ($month === 7) {
            return $isLeap ? 'אדר ב׳' : 'אדר';
        }

        return $months[$month] ?? 'חודש לא ידוע';
    }

    /**
     * Parse Hebrew date string if converter returns string
     *
     * @return array{year: int, month: int, day: int}
     */
    protected function parseHebrewString(string $date): array
    {
        $parts = preg_split('/[\s\-:]/', trim($date)) ?: [];

        $year = (int) ($parts[0] ?? 0);
        $month = (int) ($parts[1] ?? 1);
        $day = (int) ($parts[2] ?? 1);

        return [
            'year' => $year,
            'month' => $month,
            'day' => $day,
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

<?php

namespace Tests\Unit;

use App\Services\HebrewDateService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class HebrewDateServiceTest extends TestCase
{
    private HebrewDateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new HebrewDateService;
    }

    public function test_consecutive_days_produce_different_hebrew_dates(): void
    {
        $dayOne = $this->service->toHebrewString(Carbon::parse('2026-01-15'));
        $dayTwo = $this->service->toHebrewString(Carbon::parse('2026-01-16'));

        $this->assertNotSame($dayOne, $dayTwo);
        $this->assertSame('26 טבת 5786', $dayOne);
        $this->assertSame('27 טבת 5786', $dayTwo);
    }

    public function test_converts_month_view_dates_in_a_month(): void
    {
        $this->assertSame('1 טבת 5786', $this->service->toHebrewString(Carbon::parse('2025-12-21')));
        $this->assertSame('26 טבת 5786', $this->service->toHebrewString(Carbon::parse('2026-01-15')));
        $this->assertSame('22 אב 5786', $this->service->toHebrewString(Carbon::parse('2026-08-05')));
    }

    public function test_converts_rosh_hashanah(): void
    {
        $this->assertSame('1 תשרי 5786', $this->service->toHebrewString(Carbon::parse('2025-09-23')));
    }

    public function test_non_leap_year_adar_is_not_adar_i(): void
    {
        // 5785 is not a leap year, so the 7th month is plain Adar
        $this->assertSame('10 אדר 5785', $this->service->toHebrewString(Carbon::parse('2025-03-10')));
        $this->assertSame('1 ניסן 5785', $this->service->toHebrewString(Carbon::parse('2025-03-30')));
    }

    public function test_leap_year_uses_adar_i_and_adar_ii(): void
    {
        // 5787 is a leap year: month 6 is Adar I, month 7 is Adar II
        $this->assertSame('3 אדר א׳ 5787', $this->service->toHebrewString(Carbon::parse('2027-02-10')));
        $this->assertSame('6 אדר ב׳ 5787', $this->service->toHebrewString(Carbon::parse('2027-03-15')));
        $this->assertSame('21 אדר ב׳ 5787', $this->service->toHebrewString(Carbon::parse('2027-03-30')));
        $this->assertSame('1 ניסן 5787', $this->service->toHebrewString(Carbon::parse('2027-04-08')));
    }

    public function test_to_hebrew_array_returns_associative_keys(): void
    {
        $hebrew = $this->service->toHebrewArray(Carbon::parse('2026-08-05'));

        $this->assertSame(['year' => 5786, 'month' => 12, 'day' => 22], $hebrew);
    }

    public function test_day_month_string_omits_the_year(): void
    {
        $this->assertSame('22 אב', $this->service->toHebrewDayMonthString(Carbon::parse('2026-08-05')));
        $this->assertSame('3 אדר א׳', $this->service->toHebrewDayMonthString(Carbon::parse('2027-02-10')));
        $this->assertSame('1 תשרי', $this->service->toHebrewDayMonthString(Carbon::parse('2025-09-23')));
    }

    public function test_august_5_2026_is_22_av(): void
    {
        $this->assertSame('22 אב 5786', $this->service->toHebrewString(Carbon::parse('2026-08-05')));
        $this->assertSame('21 אב 5786', $this->service->toHebrewString(Carbon::parse('2026-08-04')));
    }

    public function test_leap_year_detection(): void
    {
        $this->assertFalse($this->service->isLeapYear(5785));
        $this->assertFalse($this->service->isLeapYear(5786));
        $this->assertTrue($this->service->isLeapYear(5787));
    }

    public function test_hebrew_month_name_omits_the_year(): void
    {
        $this->assertSame('טבת', $this->service->hebrewMonthName(Carbon::parse('2026-01-15')));
        $this->assertSame('שבט', $this->service->hebrewMonthName(Carbon::parse('2026-02-01')));
        $this->assertSame('אדר', $this->service->hebrewMonthName(Carbon::parse('2026-03-01')));
        $this->assertSame('אדר א׳', $this->service->hebrewMonthName(Carbon::parse('2027-02-10')));
        $this->assertSame('אדר ב׳', $this->service->hebrewMonthName(Carbon::parse('2027-03-15')));
    }
}

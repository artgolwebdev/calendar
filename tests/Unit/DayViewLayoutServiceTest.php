<?php

namespace Tests\Unit;

use App\Services\DayViewLayoutService;
use PHPUnit\Framework\TestCase;

class DayViewLayoutServiceTest extends TestCase
{
    private DayViewLayoutService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DayViewLayoutService;
    }

    public function test_single_event_spans_the_full_width(): void
    {
        $result = $this->service->layout([
            ['start' => 540, 'end' => 630],
        ]);

        $this->assertCount(1, $result);
        $this->assertSame(37.5, $result[0]['top']);
        $this->assertSame(6.25, $result[0]['height']);
        $this->assertSame(100.0, $result[0]['width']);
        $this->assertSame(0.0, $result[0]['left']);
    }

    public function test_overlapping_events_are_side_by_side(): void
    {
        $result = $this->service->layout([
            ['start' => 540, 'end' => 600, 'id' => 'a'],
            ['start' => 570, 'end' => 630, 'id' => 'b'],
        ]);

        $this->assertSame(50.0, $result[0]['width']);
        $this->assertSame(0.0, $result[0]['left']);
        $this->assertSame(50.0, $result[1]['width']);
        $this->assertSame(50.0, $result[1]['left']);
    }

    public function test_non_overlapping_events_stay_in_the_same_column(): void
    {
        $result = $this->service->layout([
            ['start' => 0, 'end' => 60, 'id' => 'a'],
            ['start' => 60, 'end' => 120, 'id' => 'b'],
        ]);

        $this->assertSame(100.0, $result[0]['width']);
        $this->assertSame(0.0, $result[0]['left']);
        $this->assertSame(100.0, $result[1]['width']);
        $this->assertSame(0.0, $result[1]['left']);
    }

    public function test_transitive_overlap_is_clustered_into_columns(): void
    {
        $result = $this->service->layout([
            ['start' => 0, 'end' => 180, 'id' => 'a'],
            ['start' => 60, 'end' => 120, 'id' => 'b'],
            ['start' => 150, 'end' => 300, 'id' => 'c'],
        ]);

        $this->assertSame('a', $result[0]['id']);
        $this->assertSame(0.0, $result[0]['left']);
        $this->assertSame('b', $result[1]['id']);
        $this->assertSame(50.0, $result[1]['left']);
        $this->assertSame('c', $result[2]['id']);
        $this->assertSame(50.0, $result[2]['left']);
    }

    public function test_events_are_clamped_to_day_bounds(): void
    {
        $result = $this->service->layout([
            ['start' => -30, 'end' => 60],
            ['start' => 1380, 'end' => 1500],
        ]);

        $this->assertSame(0.0, $result[0]['top']);
        $this->assertSame(95.8333, $result[1]['top']);
        $this->assertSame(4.1667, $result[1]['height']);
    }

    public function test_minimum_duration_is_enforced(): void
    {
        $result = $this->service->layout([
            ['start' => 600, 'end' => 610],
            ['start' => 700, 'end' => 700],
        ]);

        $this->assertSame(2.0833, $result[0]['height']);
        $this->assertSame(2.0833, $result[1]['height']);
    }

    public function test_extra_event_attributes_are_preserved(): void
    {
        $result = $this->service->layout([
            ['start' => 540, 'end' => 600, 'title' => 'מקורי'],
        ]);

        $this->assertSame('מקורי', $result[0]['title']);
    }
}

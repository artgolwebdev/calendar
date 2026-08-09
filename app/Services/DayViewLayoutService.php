<?php

namespace App\Services;

class DayViewLayoutService
{
    public const DAY_MINUTES = 1440;

    public const MIN_DURATION_MINUTES = 30;

    /**
     * Position timed events for the day view.
     *
     * Events are grouped into clusters of transitively-overlapping events,
     * then laid out in side-by-side columns within each cluster.
     *
     * @param  array<int, array{start: int, end: int}>  $events
     * @return array<int, array<string, int|float>>
     */
    public function layout(array $events): array
    {
        $normalized = array_map(fn (array $event) => $this->clamp($event), $events);

        usort($normalized, function (array $a, array $b): int {
            return $a['start'] <=> $b['start'] ?: $b['end'] <=> $a['end'];
        });

        $positioned = [];
        $cluster = [];
        $clusterEnd = null;

        foreach ($normalized as $event) {
            if ($clusterEnd !== null && $event['start'] >= $clusterEnd) {
                $positioned = array_merge($positioned, $this->layoutCluster($cluster));
                $cluster = [];
                $clusterEnd = null;
            }

            $cluster[] = $event;
            $clusterEnd = $clusterEnd === null ? $event['end'] : max($clusterEnd, $event['end']);
        }

        if ($cluster !== []) {
            $positioned = array_merge($positioned, $this->layoutCluster($cluster));
        }

        return $positioned;
    }

    /**
     * Clamp an event to the visible day range and normalise its duration.
     *
     * @param  array{start: int, end: int}  $event
     * @return array<string, int>
     */
    private function clamp(array $event): array
    {
        $start = max(0, min((int) $event['start'], self::DAY_MINUTES));
        $end = max(0, min((int) $event['end'], self::DAY_MINUTES));

        if ($end <= $start) {
            $end = min($start + self::MIN_DURATION_MINUTES, self::DAY_MINUTES);
        }

        return ['start' => $start, 'end' => $end] + $event;
    }

    /**
     * Lay out a cluster of overlapping events into side-by-side columns.
     *
     * @param  array<int, array{start: int, end: int}>  $cluster
     * @return array<int, array<string, int|float>>
     */
    private function layoutCluster(array $cluster): array
    {
        $columnEnds = [];
        $positioned = [];

        foreach ($cluster as $event) {
            $column = $this->firstFreeColumn($columnEnds, $event['start']);
            $columnEnds[$column] = $event['end'];

            $positioned[] = $event + ['column' => $column];
        }

        $maxColumns = max(array_column($positioned, 'column')) + 1;

        foreach ($positioned as &$event) {
            $duration = max($event['end'] - $event['start'], self::MIN_DURATION_MINUTES);

            $event['top'] = round($event['start'] / self::DAY_MINUTES * 100, 4);
            $event['height'] = round($duration / self::DAY_MINUTES * 100, 4);
            $event['width'] = round(100 / $maxColumns, 4);
            $event['left'] = round($event['column'] * (100 / $maxColumns), 4);

            unset($event['column']);
        }
        unset($event);

        return $positioned;
    }

    /**
     * First column whose last event ends on or before the given start minute.
     *
     * @param  array<int, int>  $columnEnds
     */
    private function firstFreeColumn(array &$columnEnds, int $start): int
    {
        foreach ($columnEnds as $column => $end) {
            if ($end <= $start) {
                return $column;
            }
        }

        return count($columnEnds);
    }
}

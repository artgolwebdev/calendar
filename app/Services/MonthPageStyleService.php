<?php

namespace App\Services;

use App\Models\MonthPage;

class MonthPageStyleService
{
    public const DEFAULT_FONT = 'default';

    public const DEFAULT_OVERLAY_OPACITY = 30;

    public const DEFAULT_DAY_BOX_BG_COLOR = '#FFFFFF';

    public const DEFAULT_DAY_BOX_FONT_COLOR = '#2B2E3A';

    public const DEFAULT_DAY_BOX_BG_OPACITY = 100;

    public const DEFAULT_WEEKDAY_COLOR = '#6B6B75';

    public const DEFAULT_SHOW_ADJACENT_MONTH_DAYS = true;

    private const FONTS = [
        'default' => "'Heebo', sans-serif",
        'modern' => "'Assistant', sans-serif",
        'traditional' => "'Frank Ruhl Libre', serif",
        'elegant' => "'Rubik', sans-serif",
    ];

    /**
     * Get the default design settings used when creating a month page.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'font_choice' => self::DEFAULT_FONT,
            'overlay_opacity' => self::DEFAULT_OVERLAY_OPACITY,
            'day_box_bg_color' => self::DEFAULT_DAY_BOX_BG_COLOR,
            'day_box_font_color' => self::DEFAULT_DAY_BOX_FONT_COLOR,
            'day_box_bg_opacity' => self::DEFAULT_DAY_BOX_BG_OPACITY,
            'weekday_color' => self::DEFAULT_WEEKDAY_COLOR,
            'show_adjacent_month_days' => self::DEFAULT_SHOW_ADJACENT_MONTH_DAYS,
        ];
    }

    /**
     * Resolve all resolved styles for a month page.
     *
     * @return array{fontFamily: string, gridBackground: string, overlay: string, weekdayColor: string, dayBox: array{backgroundColor: string, fontColor: string}}
     */
    public function resolve(MonthPage $monthPage): array
    {
        return [
            'fontFamily' => $this->fontFamily($monthPage),
            'gridBackground' => $this->gridBackground($monthPage),
            'overlay' => $this->overlay($monthPage),
            'weekdayColor' => $this->weekdayColor($monthPage),
            'dayBox' => $this->dayBox($monthPage),
        ];
    }

    public function fontFamily(MonthPage $monthPage): string
    {
        return self::FONTS[$monthPage->font_choice] ?? self::FONTS[self::DEFAULT_FONT];
    }

    public function gridBackground(MonthPage $monthPage): string
    {
        if ($monthPage->background_media_id && $media = $monthPage->backgroundMedia) {
            $url = $media->getUrl();

            return "background-image: url('{$url}'); background-size: cover; background-position: center; background-repeat: no-repeat;";
        }

        $path = $monthPage->custom_image_path ?? $monthPage->background_image_path;

        if (! $path) {
            return '';
        }

        $url = asset('storage/'.$path);

        return "background-image: url('{$url}'); background-size: cover; background-position: center; background-repeat: no-repeat;";
    }

    public function overlay(MonthPage $monthPage): string
    {
        $opacity = $monthPage->overlay_opacity ?? self::DEFAULT_OVERLAY_OPACITY;

        return 'background-color: rgba(0, 0, 0, '.($opacity / 100).');';
    }

    public function weekdayColor(MonthPage $monthPage): string
    {
        return $monthPage->weekday_color ?? self::DEFAULT_WEEKDAY_COLOR;
    }

    /**
     * @return array{backgroundColor: string, fontColor: string}
     */
    public function dayBox(MonthPage $monthPage): array
    {
        $bgColor = $monthPage->day_box_bg_color ?? self::DEFAULT_DAY_BOX_BG_COLOR;
        $fontColor = $monthPage->day_box_font_color ?? self::DEFAULT_DAY_BOX_FONT_COLOR;
        $bgOpacity = $monthPage->day_box_bg_opacity ?? self::DEFAULT_DAY_BOX_BG_OPACITY;

        return [
            'backgroundColor' => $this->hexToRgba($bgColor, $bgOpacity / 100),
            'fontColor' => $fontColor,
        ];
    }

    /**
     * Convert a hex color to an rgba string.
     */
    private function hexToRgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }
}

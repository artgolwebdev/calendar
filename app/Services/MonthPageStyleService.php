<?php

namespace App\Services;

use App\Models\MonthPage;

class MonthPageStyleService
{
    /**
     * Resolve all styles for a month page
     */
    public function resolve(MonthPage $monthPage): array
    {
        return [
            'fontFamily' => $this->getFontFamily($monthPage),
            'gridBackgroundStyle' => $this->getGridBackgroundStyle($monthPage),
            'overlayStyle' => $this->getOverlayStyle($monthPage),
            'dayBoxStyle' => $this->getDayBoxStyle($monthPage),
        ];
    }

    /**
     * Get font family based on font choice
     */
    private function getFontFamily(MonthPage $monthPage): string
    {
        $fontMap = [
            'default' => "'Heebo', sans-serif",
            'modern' => "'Assistant', sans-serif",
            'traditional' => "'Frank Ruhl Libre', serif",
            'elegant' => "'Rubik', sans-serif",
        ];

        return $fontMap[$monthPage->font_choice] ?? $fontMap['default'];
    }

    /**
     * Get grid background style
     */
    private function getGridBackgroundStyle(MonthPage $monthPage): string
    {
        $backgroundImage = '';
        
        if ($monthPage->custom_image_path) {
            $backgroundImage = asset('storage/' . $monthPage->custom_image_path);
        } elseif ($monthPage->background_image_path) {
            $backgroundImage = asset('storage/' . $monthPage->background_image_path);
        }

        if ($backgroundImage) {
            return "background-image: url('$backgroundImage'); background-size: cover; background-position: center; background-repeat: no-repeat;";
        }

        return '';
    }

    /**
     * Get overlay style
     */
    private function getOverlayStyle(MonthPage $monthPage): string
    {
        $opacity = $monthPage->overlay_opacity ?? 30;
        return "background-color: rgba(0, 0, 0, " . ($opacity / 100) . ");";
    }

    /**
     * Get day box style
     */
    private function getDayBoxStyle(MonthPage $monthPage): array
    {
        $bgColor = $monthPage->day_box_bg_color ?? '#FFFFFF';
        $fontColor = $monthPage->day_box_font_color ?? '#2B2E3A';
        $bgOpacity = $monthPage->day_box_bg_opacity ?? 100;

        // Convert hex to rgba for background color with opacity
        $bgRgba = $this->hexToRgba($bgColor, $bgOpacity / 100);

        return [
            'backgroundColor' => $bgRgba,
            'fontColor' => $fontColor,
        ];
    }

    /**
     * Convert hex color to rgba
     */
    private function hexToRgba(string $hex, float $alpha): string
    {
        // Remove hash if present
        $hex = ltrim($hex, '#');
        
        // Parse hex
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "rgba($r, $g, $b, $alpha)";
    }
}
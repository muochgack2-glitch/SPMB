<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format bytes to human readable size
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        if ($bytes === null || $bytes < 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Format duration in seconds to human readable
     */
    public static function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return round($seconds, 2) . 's';
        }

        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        if ($minutes < 60) {
            return $minutes . 'm ' . round($seconds) . 's';
        }

        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;

        return $hours . 'h ' . $minutes . 'm';
    }

    /**
     * Format number with thousand separator
     */
    public static function formatNumber($number, $decimals = 0)
    {
        return number_format($number, $decimals, '.', ',');
    }
}

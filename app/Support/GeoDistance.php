<?php

namespace App\Support;

class GeoDistance
{
    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 6371.0 * 2.0 * atan2(sqrt($a), sqrt(1.0 - $a));
    }

    public static function formatKm(float $km): string
    {
        if ($km < 1.0) {
            return round($km * 1000) . ' m de você';
        }

        return number_format($km, 1, ',', '.') . ' km de você';
    }
}

<?php


namespace App\Utils;

use Carbon\Carbon;

class DateFormatter
{
    public static function formatDate(?string $date, string $format = 'Y-m-d'): ?string
    {
        if (!$date) {
            return null;
        }

        $timezone = date_default_timezone_get();
        return Carbon::parse($date)->setTimezone($timezone)->format($format);
    }

    public static function formatDateTime(?string $dateTime, string $format = 'Y-m-d H:i:s'): ?string
    {
        if (!$dateTime) {
            return null;
        }

        $timezone = date_default_timezone_get();
        return Carbon::parse($dateTime)->setTimezone($timezone)->format($format);
    }
}

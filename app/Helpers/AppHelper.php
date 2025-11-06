<?php

namespace App\Helpers;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Carbon;

class AppHelper
{

    public static function convert_user_date_timezone($date)
    {
        $date = self::response_date_format($date, 'Australia/Sydney');
        return $date['user'];
    }

    public static function response_date_format($date, $timezone, $format = null)
    {
        $system_timezone = 'UTC';
        $system_datetime_format = 'Y-m-d H:i:s';
        $display_datetime_format = 'M d, Y h:i A';
        $timezone = (!empty($timezone)) ? $timezone : $system_timezone;
        if (!empty($format) && $format === true) {
            $format = $display_datetime_format;
        } else if (!empty($format)) {
            $format = $format;
        } else {
            $format = $system_datetime_format;
        }

        if (!empty($date)) {
            $date_obj = new DateTime($date, new DateTimeZone($system_timezone));
            $utc_tz = new DateTimeZone("UTC");
            $utc_time = $date_obj->setTimezone($utc_tz)->format($system_datetime_format);
            $temp_date = Carbon::createFromFormat($system_datetime_format, $utc_time, 'UTC')->setTimezone($timezone);
            return [
                'utc' => $utc_time,
                'user' => $temp_date->format($format),
                'timezone' => $timezone,
                'timezone_code' => $temp_date->format('T'),
                'display_format' => $temp_date->format('d-M-y h:i a, T'),
            ];
        } else {
            return null;
        }
    }

    public static function convertTimezone($date, $fromZone = "", $toZone = "UTC")
    {
        $duetimegiven = new DateTime($date, new DateTimeZone($toZone));
        if ($fromZone) {
            $duetimegiven->setTimezone(new DateTimeZone($fromZone));
        }
        return $duetimegiven->format("Y-m-d H:i:s");
    }
}

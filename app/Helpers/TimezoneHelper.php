<?php

namespace App\Helpers;

class TimezoneHelper
{
    protected static $timezoneFixMap = [
        // 🇮🇳 India
    'asia/culcatta'               => 'Asia/Kolkata',
    'asia/calcutta'               => 'Asia/Kolkata',
    'ist'                         => 'Asia/Kolkata',
    'gmt+5:30'                    => 'Asia/Kolkata',
    'indian standard time'        => 'Asia/Kolkata',
    // 🇵🇰 Pakistan
    'pakistan standard time'      => 'Asia/Karachi',
    // 🇧🇩 Bangladesh
    'bangladesh standard time'    => 'Asia/Dhaka',
    // 🇨🇳 China
    'china standard time'         => 'Asia/Shanghai',
    'beijing time'                => 'Asia/Shanghai',
    // 🇭🇰 Hong Kong
    'hong kong time'              => 'Asia/Hong_Kong',
    // 🇯🇵 Japan
    'tokyo standard time'         => 'Asia/Tokyo',
    'japan standard time'         => 'Asia/Tokyo',
    // 🇰🇷 South Korea
    'korean standard time'        => 'Asia/Seoul',
    // 🇹🇭 Thailand
    'indochina time'              => 'Asia/Bangkok',
    // 🇸🇦 Saudi Arabia
    'arabian standard time'       => 'Asia/Riyadh',
    // 🇷🇺 Russia
    'moscow standard time'        => 'Europe/Moscow',
    'utc+3'                       => 'Europe/Moscow',
    // 🇬🇧 UK
    'gmt'                         => 'Europe/London',
    'bst'                         => 'Europe/London', // British Summer Time
    // 🇪🇺 Europe
    'cet'                         => 'Europe/Berlin',
    'central european time'       => 'Europe/Berlin',
    'eastern european time'       => 'Europe/Bucharest',
    // 🇺🇸 USA
    'est'                         => 'America/New_York',
    'edt'                         => 'America/New_York',
    'eastern standard time'       => 'America/New_York',
    'cst'                         => 'America/Chicago',
    'cdt'                         => 'America/Chicago',
    'central standard time'       => 'America/Chicago',
    'mst'                         => 'America/Denver',
    'mdt'                         => 'America/Denver',
    'mountain standard time'      => 'America/Denver',
    'pst'                         => 'America/Los_Angeles',
    'pdt'                         => 'America/Los_Angeles',
    'pacific standard time'       => 'America/Los_Angeles',
    // 🇲🇽 Mexico
    'mexico standard time'        => 'America/Mexico_City',
    // 🌍 Generic abbreviations
    'utc'                         => 'UTC',
    'utc+0'                       => 'UTC',
    'utc+1'                       => 'Europe/Paris',
    'utc+2'                       => 'Europe/Kiev',
    'utc+4'                       => 'Asia/Dubai',
    'utc+5'                       => 'Asia/Karachi',
    'utc+5:30'                    => 'Asia/Kolkata',
    'utc+6'                       => 'Asia/Dhaka',
    'utc+7'                       => 'Asia/Bangkok',
    'utc+8'                       => 'Asia/Singapore',
    'utc+9'                       => 'Asia/Tokyo',
    'utc+10'                      => 'Australia/Sydney',
    'utc+12'                      => 'Pacific/Auckland',
        // Add more custom aliases or fixes as needed
    ];

    public static function sanitize($timezone)
    {
        $timezone = strtolower(trim($timezone));

        // Fix from map
        if (isset(self::$timezoneFixMap[$timezone])) {
            return self::$timezoneFixMap[$timezone];
        }

        // Validate against all PHP supported timezones
        foreach (timezone_identifiers_list() as $validTimezone) {
            if (strtolower($validTimezone) === $timezone) {
                return $validTimezone;
            }
        }

        // Default fallback
        return config('app.timezone', 'UTC');
    }
}

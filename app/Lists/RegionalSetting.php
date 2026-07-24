<?php

namespace App\Lists;

class RegionalSetting
{
    public const TIMEZONES = [
        'Asia/Aden',
        'Asia/Riyadh',
        'Asia/Qatar',
    ];

    public const TIMEZONE_LABELS = [
        'Asia/Aden' => 'Yemen (Asia/Aden)',
        'Asia/Riyadh' => 'Saudi Arabia (Asia/Riyadh)',
        'Asia/Qatar' => 'Qatar (Asia/Qatar)',
    ];

    public const CURRENCIES = [
        'USD',
        'SAR',
        'YER',
    ];

    public const DEFAULT_TIMEZONE = 'Asia/Aden';

    public const DEFAULT_CURRENCY = 'USD';
}

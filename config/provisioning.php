<?php

return [
    'team_name' => env('DEFAULT_TEAM_NAME', 'Default'),

    'academic_period' => [
        'name' => env('DEFAULT_PERIOD_NAME'),
        'code' => env('DEFAULT_PERIOD_CODE'),
        'shortcode' => env('DEFAULT_PERIOD_SHORTCODE'),
        'alias' => env('DEFAULT_PERIOD_ALIAS'),
        'start_date' => env('DEFAULT_PERIOD_START_DATE'),
        'end_date' => env('DEFAULT_PERIOD_END_DATE'),
    ],
];

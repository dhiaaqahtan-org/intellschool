<?php

namespace App\Enums;

use App\Concerns\HasEnum;

enum Gender: string
{
    use HasEnum;

    case MALE = 'male';
    case FEMALE = 'female';

    public static function translation(): string
    {
        return 'list.genders.';
    }
}

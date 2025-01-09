<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Enums;

use App\Modules\Databank\Common\Contracts\HumanReadableEnum;

enum MediaType: int implements HumanReadableEnum
{
    case MOVIE = 1;
    case SERIES = 2;
    case GAME = 3;

    public function nameForHumans(): string
    {
        return match ($this) {
            self::MOVIE => __('Movies'),
            self::SERIES => __('Series'),
            self::GAME => __('Games'),
        };
    }
}

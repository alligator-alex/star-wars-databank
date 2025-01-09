<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Enums;

use App\Modules\Databank\Common\Contracts\HumanReadableEnum;

enum Status: int implements HumanReadableEnum
{
    case DRAFT = 1;
    case PUBLISHED = 2;

    public function nameForHumans(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
        };
    }
}

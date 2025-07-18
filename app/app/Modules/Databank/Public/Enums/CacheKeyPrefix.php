<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Enums;

enum CacheKeyPrefix: string
{
    case EXPLORE = 'public:explore';
}

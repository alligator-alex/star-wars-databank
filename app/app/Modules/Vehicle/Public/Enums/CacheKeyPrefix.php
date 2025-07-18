<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Public\Enums;

enum CacheKeyPrefix: string
{
    case PAGINATED = 'public:vehicles:paginated';
    case ONE = 'public:vehicles:one:slug';
    case RANDOM = 'public:vehicles:random';
}

<?php

declare(strict_types=1);

namespace App\Modules\Droid\Public\Enums;

enum CacheKeyPrefix: string
{
    case PAGINATED = 'public:droids:paginated';
    case ONE = 'public:droids:one:slug';
    case RANDOM = 'public:droids:random';
}

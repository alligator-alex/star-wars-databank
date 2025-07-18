<?php

declare(strict_types=1);

namespace App\Modules\Media\Public\Enums;

enum CacheKeyPrefix: string
{
    case AVAILABLE_TYPES = 'public:media:available-types';
    case ALL = 'public:media:all';
    case DROPDOWN_LIST = 'public:media:dropdown-list';
}

<?php

declare(strict_types=1);

namespace App\Modules\Faction\Public\Enums;

enum CacheKeyPrefix: string
{
    case ALL = 'public:factions:all';
    case DROPDOWN_LIST = 'public:factions:dropdown-list';
}

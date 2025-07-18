<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Public\Enums;

enum CacheKeyPrefix: string
{
    case DROPDOWN_LIST = 'public:handbook-values:dropdown-list';
}

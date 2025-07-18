<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Public\Enums;

enum CacheKeyPrefix: string
{
    case DROPDOWN_LIST = 'public:manufacturers:dropdown-list';
}

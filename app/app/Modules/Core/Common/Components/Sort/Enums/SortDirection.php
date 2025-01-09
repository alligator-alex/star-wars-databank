<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Components\Sort\Enums;

enum SortDirection: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}

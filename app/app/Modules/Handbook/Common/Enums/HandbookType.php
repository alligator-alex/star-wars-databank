<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Enums;

enum HandbookType: int
{
    case VEHICLE_CATEGORY = 1;
    case VEHICLE_TYPE = 2;
    case VEHICLE_LINE = 3;

    case DROID_LINE = 5;
    case DROID_MODEL = 6;
    case DROID_CLASS = 7;
}

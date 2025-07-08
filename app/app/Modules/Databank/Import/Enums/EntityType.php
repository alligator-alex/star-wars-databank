<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\Enums;

enum EntityType: string
{
    case VEHICLE = 'vehicle';
    case DROID = 'droid';
}

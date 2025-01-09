<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Enums;

enum VehicleRouteName: string
{
    case LIST = 'public.vehicle.list';
    case ONE = 'public.vehicle.one';
}

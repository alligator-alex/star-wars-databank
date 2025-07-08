<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Public\Enums;

enum VehicleRouteName: string
{
    case INDEX = 'public.vehicle.index';
    case DETAIL = 'public.vehicle.detail';
}

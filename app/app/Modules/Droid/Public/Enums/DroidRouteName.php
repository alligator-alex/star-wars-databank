<?php

declare(strict_types=1);

namespace App\Modules\Droid\Public\Enums;

enum DroidRouteName: string
{
    case INDEX = 'public.droid.index';
    case DETAIL = 'public.droid.detail';
}

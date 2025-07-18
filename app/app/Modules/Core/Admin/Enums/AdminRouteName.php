<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Enums;

enum AdminRouteName: string
{
    case HOME = 'platform.home';

    case SETTINGS = 'platform.settings';
    case CLEAR_CACHE = 'platform.settings.clear-cache';
}

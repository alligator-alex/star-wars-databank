<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Enums;

enum UserRouteName: string
{
    case PROFILE = 'platform.user.profile';

    case UPDATE = 'platform.user.update';
    case CHANGE_PASSWORD = 'platform.user.change-password';
}

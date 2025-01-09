<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Enums;

enum MediaRouteName: string
{
    case LIST = 'platform.media.list';
    case ONE = 'platform.media.one';
    case NEW = 'platform.media.new';

    case CREATE = 'platform.media.create';
    case UPDATE = 'platform.media.update';
    case DELETE = 'platform.media.delete';

    case TOGGLE_PUBLISH = 'platform.media.toggle-publish';
}

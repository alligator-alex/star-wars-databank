<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Enums;

enum LineRouteName: string
{
    case LIST = 'platform.line.list';
    case ONE = 'platform.line.one';
    case NEW = 'platform.line.new';

    case CREATE = 'platform.line.create';
    case UPDATE = 'platform.line.update';
    case DELETE = 'platform.line.delete';

    case TOGGLE_PUBLISH = 'platform.line.toggle-publish';
}

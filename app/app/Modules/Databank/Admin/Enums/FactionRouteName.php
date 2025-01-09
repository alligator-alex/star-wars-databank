<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Enums;

enum FactionRouteName: string
{
    case LIST = 'platform.faction.list';
    case ONE = 'platform.faction.one';
    case NEW = 'platform.faction.new';

    case CREATE = 'platform.faction.create';
    case UPDATE = 'platform.faction.update';
    case DELETE = 'platform.faction.delete';

    case TOGGLE_PUBLISH = 'platform.faction.toggle-publish';
}

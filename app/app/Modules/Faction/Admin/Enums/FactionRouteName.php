<?php

declare(strict_types=1);

namespace App\Modules\Faction\Admin\Enums;

enum FactionRouteName: string
{
    case INDEX = 'platform.faction.index';
    case EDIT = 'platform.faction.edit';
    case CREATE = 'platform.faction.create';

    case STORE = 'platform.faction.store';
    case UPDATE = 'platform.faction.update';
    case DELETE = 'platform.faction.delete';
    case TOGGLE_PUBLISH = 'platform.faction.toggle-publish';
}

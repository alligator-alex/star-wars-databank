<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Enums;

enum VehicleRouteName: string
{
    case LIST = 'platform.vehicle.list';
    case ONE = 'platform.vehicle.one';
    case NEW = 'platform.vehicle.new';

    case CREATE = 'platform.vehicle.create';
    case UPDATE = 'platform.vehicle.update';
    case DELETE = 'platform.vehicle.delete';

    case TOGGLE_PUBLISH = 'platform.vehicle.toggle-publish';

    case UPDATE_ONE_PAGE_SETTINGS = 'platform.vehicle.page-settings.one';
    case UPDATE_LIST_PAGE_SETTINGS = 'platform.vehicle.page-settings.list';
}

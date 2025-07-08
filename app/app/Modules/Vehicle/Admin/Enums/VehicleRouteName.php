<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Enums;

enum VehicleRouteName: string
{
    case INDEX = 'platform.vehicle.index';
    case EDIT = 'platform.vehicle.edit';
    case CREATE = 'platform.vehicle.create';

    case STORE = 'platform.vehicle.store';
    case UPDATE = 'platform.vehicle.update';
    case DELETE = 'platform.vehicle.delete';

    case TOGGLE_PUBLISH = 'platform.vehicle.toggle-publish';

    case UPDATE_INDEX_PAGE_SETTINGS = 'platform.vehicle.page-settings.index.update';
    case UPDATE_DETAIL_PAGE_SETTINGS = 'platform.vehicle.page-settings.detail.update';
}

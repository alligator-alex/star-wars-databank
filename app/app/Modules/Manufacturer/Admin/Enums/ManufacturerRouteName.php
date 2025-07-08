<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Admin\Enums;

enum ManufacturerRouteName: string
{
    case INDEX = 'platform.manufacturer.index';
    case EDIT = 'platform.manufacturer.edit';
    case CREATE = 'platform.manufacturer.create';

    case STORE = 'platform.manufacturer.store';
    case UPDATE = 'platform.manufacturer.update';
    case DELETE = 'platform.manufacturer.delete';
    case TOGGLE_PUBLISH = 'platform.manufacturer.toggle-publish';
}

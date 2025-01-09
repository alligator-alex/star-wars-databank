<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Enums;

enum ManufacturerRouteName: string
{
    case LIST = 'platform.manufacturer.list';
    case ONE = 'platform.manufacturer.one';
    case NEW = 'platform.manufacturer.new';

    case CREATE = 'platform.manufacturer.create';
    case UPDATE = 'platform.manufacturer.update';
    case DELETE = 'platform.manufacturer.delete';

    case TOGGLE_PUBLISH = 'platform.manufacturer.toggle-publish';
}

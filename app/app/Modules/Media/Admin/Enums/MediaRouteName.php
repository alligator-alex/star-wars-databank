<?php

declare(strict_types=1);

namespace App\Modules\Media\Admin\Enums;

enum MediaRouteName: string
{
    case INDEX = 'platform.media.index';
    case EDIT = 'platform.media.edit';
    case CREATE = 'platform.media.create';

    case STORE = 'platform.media.store';
    case UPDATE = 'platform.media.update';
    case DELETE = 'platform.media.delete';

    case TOGGLE_PUBLISH = 'platform.media.toggle-publish';
}

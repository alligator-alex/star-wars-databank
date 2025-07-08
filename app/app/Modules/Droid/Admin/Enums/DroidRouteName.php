<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Enums;

enum DroidRouteName: string
{
    case INDEX = 'platform.droid.index';
    case EDIT = 'platform.droid.edit';
    case CREATE = 'platform.droid.create';

    case STORE = 'platform.droid.store';
    case UPDATE = 'platform.droid.update';
    case DELETE = 'platform.droid.delete';

    case TOGGLE_PUBLISH = 'platform.droid.toggle-publish';

    case UPDATE_INDEX_PAGE_SETTINGS = 'platform.droid.page-settings.index.update';
    case UPDATE_DETAIL_PAGE_SETTINGS = 'platform.droid.page-settings.detail.update';
}

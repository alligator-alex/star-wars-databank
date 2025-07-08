<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Admin\Enums;

enum HandbookValueRouteName: string
{
    case INDEX = 'platform.handbook-value.index';
    case EDIT = 'platform.handbook-value.edit';
    case CREATE = 'platform.handbook-value.create';

    case STORE = 'platform.handbook-value.store';
    case UPDATE = 'platform.handbook-value.update';
    case DELETE = 'platform.handbook-value.delete';
}

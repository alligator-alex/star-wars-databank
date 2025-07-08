<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Enums;

enum AttachmentGroup: string
{
    case VEHICLE_IMAGE = 'vehicle-image';
    case DROID_IMAGE = 'droid-image';
    case MEDIA_POSTER = 'media-poster';
}

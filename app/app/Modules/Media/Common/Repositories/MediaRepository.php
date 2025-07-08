<?php

declare(strict_types=1);

namespace App\Modules\Media\Common\Repositories;

use App\Modules\Databank\Common\Repositories\BaseRepository;
use App\Modules\Media\Common\Models\Media;

/**
 * @extends BaseRepository<Media>
 */
class MediaRepository extends BaseRepository
{
    protected string $modelClass = Media::class;
}

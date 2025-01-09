<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Repositories;

use App\Modules\Databank\Common\Models\Line;

/**
 * @extends BaseRepository<Line>
 */
class LineRepository extends BaseRepository
{
    protected string $modelClass = Line::class;
}

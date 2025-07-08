<?php

declare(strict_types=1);

namespace App\Modules\Faction\Common\Repositories;

use App\Modules\Databank\Common\Repositories\BaseRepository;
use App\Modules\Faction\Common\Models\Faction;

/**
 * @extends BaseRepository<Faction>
 */
class FactionRepository extends BaseRepository
{
    protected string $modelClass = Faction::class;
}

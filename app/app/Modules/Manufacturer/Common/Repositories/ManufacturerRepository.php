<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Common\Repositories;

use App\Modules\Databank\Common\Repositories\BaseRepository;
use App\Modules\Manufacturer\Common\Models\Manufacturer;

/**
 * @extends BaseRepository<Manufacturer>
 */
class ManufacturerRepository extends BaseRepository
{
    protected string $modelClass = Manufacturer::class;
}

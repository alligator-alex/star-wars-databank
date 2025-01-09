<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Repositories;

use App\Modules\Databank\Common\Models\Manufacturer;

/**
 * @extends BaseRepository<Manufacturer>
 */
class ManufacturerRepository extends BaseRepository
{
    protected string $modelClass = Manufacturer::class;
}

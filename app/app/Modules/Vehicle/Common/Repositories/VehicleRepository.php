<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\Repositories;

use App\Modules\Databank\Common\Repositories\BaseRepository;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<Vehicle>
 */
class VehicleRepository extends BaseRepository
{
    protected string $modelClass = Vehicle::class;

    /**
     * Find model by external URL.
     *
     * @param string $externalUrl
     * @param bool $withDrafts
     *
     * @return Vehicle|null
     */
    public function findByExternalUrl(string $externalUrl, bool $withDrafts = false): ?Vehicle
    {
        /** @var Builder<Vehicle>|Vehicle $query */
        $query = $this->queryBuilder();

        if ($withDrafts) {
            $query->withDrafts();
        }

        /** @var Vehicle|null $model */
        $model = $query
            ->where('external_url', '=', $externalUrl)
            ->first();

        return $model;
    }
}

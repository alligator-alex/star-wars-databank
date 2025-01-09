<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Repositories;

use App\Modules\Databank\Common\Models\Vehicle;

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
        $query = $this->getQueryBuilder();

        if ($withDrafts) {
            /** @phpstan-ignore-next-line */
            $query->withDrafts();
        }

        return $query
            ->where('external_url', '=', $externalUrl)
            ->first();
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Droid\Common\Repositories;

use App\Modules\Databank\Common\Repositories\BaseRepository;
use App\Modules\Droid\Common\Models\Droid;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<Droid>
 */
class DroidRepository extends BaseRepository
{
    protected string $modelClass = Droid::class;

    /**
     * Find model by external URL.
     *
     * @param string $externalUrl
     * @param bool $withDrafts
     *
     * @return Droid|null
     */
    public function findByExternalUrl(string $externalUrl, bool $withDrafts = false): ?Droid
    {
        /** @var Builder<Droid>|Droid $query */
        $query = $this->queryBuilder();

        if ($withDrafts) {
            $query->withDrafts();
        }

        /** @var Droid|null $model */
        $model = $query
            ->where('external_url', '=', $externalUrl)
            ->first();

        return $model;
    }
}

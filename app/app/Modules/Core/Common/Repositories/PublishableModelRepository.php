<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Repositories;

use App\Modules\Core\Common\Components\Model;

/**
 * @template TModel of Model
 * @extends ModelRepository<TModel>
 */
abstract class PublishableModelRepository extends ModelRepository
{
    /**
     * Find model by ID.
     *
     * @param int $id
     * @param bool $withDrafts
     *
     * @return Model|null
     */
    public function findOneById(int $id, bool $withDrafts = false): ?Model
    {
        $query = $this->queryBuilder()
            ->where('id', '=', $id);

        if ($withDrafts) {
            /** @phpstan-ignore-next-line */
            $query->withDrafts();
        }

        return $query->first();
    }
}

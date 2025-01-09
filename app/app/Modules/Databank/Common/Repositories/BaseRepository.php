<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Repositories;

use App\Modules\Core\Common\Components\Model;
use App\Modules\Core\Common\Repositories\PublishableModelRepository;

/**
 * @template TModel of Model
 * @extends PublishableModelRepository<TModel>
 */
abstract class BaseRepository extends PublishableModelRepository
{
    /**
     * Find model by name.
     *
     * @param string $name
     * @param bool $withDrafts
     *
     * @return TModel|null
     */
    public function findByName(string $name, bool $withDrafts = false): ?Model
    {
        $query = $this->getQueryBuilder();

        if ($withDrafts) {
            /** @phpstan-ignore-next-line */
            $query->withDrafts();
        }

        $query->where('name', '=', $name);

        /** @var TModel|null $model */
        $model = $query->first();

        return $model;
    }

    /**
     * Find model by slug.
     *
     * @param string $slug
     * @param bool $withDrafts
     *
     * @return Model|null
     */
    public function findBySlug(string $slug, bool $withDrafts = false): ?Model
    {
        $query = $this->getQueryBuilder();

        if ($withDrafts) {
            /** @phpstan-ignore-next-line */
            $query->withDrafts();
        }

        $query->where('slug', '=', $slug);

        /** @var TModel|null $model */
        $model = $query->first();

        return $model;
    }
}

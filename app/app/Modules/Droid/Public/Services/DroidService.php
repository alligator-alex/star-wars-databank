<?php

declare(strict_types=1);

namespace App\Modules\Droid\Public\Services;

use App\Modules\Droid\Common\Contracts\DroidFilter;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

// TODO: cache
class DroidService
{
    public const int PER_PAGE = 27;

    public function __construct(private readonly DroidRepository $repository)
    {
    }

    /**
     * Get count of all models.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->repository->queryBuilder()->count();
    }

    /**
     * Get all models query.
     *
     * @param DroidFilter|null $filter
     *
     * @return Builder
     */
    public function queryBuilder(?DroidFilter $filter = null): Builder
    {
        /** @var Builder|Droid $query */
        $query = $this->repository->queryBuilder();

        $query->with([
            'image',
            'line',
            'model',
            'class',
            'mainFaction',
            'otherFactions',
        ]);

        if ($filter) {
            $this->applyFilters($query, $filter);
        }

        $query->orderBy('name');
        $query->orderBy('sort');
        $query->orderByDesc('id');

        return $query;
    }

    /**
     * Find models with pagination and optional filter.
     *
     * @param DroidFilter $filter
     *
     * @return LengthAwarePaginator
     */
    public function findPaginated(DroidFilter $filter): LengthAwarePaginator
    {
        return $this->queryBuilder($filter)->paginate(static::PER_PAGE);
    }

    private function applyFilters(Builder $query, DroidFilter $filter): void
    {
        if ($filter->getFactions()) {
            $query->whereHas(
                'factions',
                fn (Builder $subQuery) => $subQuery->whereIn(Faction::tableName() . '.slug', $filter->getFactions())
            );
        }

        if ($filter->getManufacturers()) {
            $query->whereHas(
                'manufacturers',
                fn (Builder $subQuery) => $subQuery->whereIn(Manufacturer::tableName() . '.slug', $filter->getManufacturers())
            );
        }

        if ($filter->getMedia()) {
            $query->whereHas(
                'appearances',
                fn (Builder $subQuery) => $subQuery->whereIn(Media::tableName() . '.slug', $filter->getMedia())
            );
        }

        if ($filter->getLines()) {
            $query->whereHas('line', fn (Builder $subQuery): Builder => $subQuery->whereIn(
                HandbookValue::tableName() . '.slug',
                $filter->getLines()
            ));
        }

        if ($filter->getModels()) {
            $query->whereHas('model', fn (Builder $subQuery): Builder => $subQuery->whereIn(
                HandbookValue::tableName() . '.slug',
                $filter->getModels()
            ));
        }

        if ($filter->getClasses()) {
            $query->whereHas('class', fn (Builder $subQuery): Builder => $subQuery->whereIn(
                HandbookValue::tableName() . '.slug',
                $filter->getClasses()
            ));
        }
    }

    public function findRandom(int $count): Collection
    {
        return $this->queryBuilder()
            ->reorder()
            ->inRandomOrder()
            ->limit($count)
            ->get();
    }

    /**
     * Find Droid model by slug.
     *
     * @param string $slug
     * @param bool $withDrafts
     *
     * @return Droid|null
     */
    public function findOneBySlug(string $slug, bool $withDrafts = false): ?Droid
    {
        /** @var Droid|null $model */
        $model = $this->repository->findOneBySlug($slug, $withDrafts);

        if (!$model) {
            return null;
        }

        $model->load([
            'image',
            'line',
            'model',
            'class',
            'manufacturers',
            'mainFaction',
            'otherFactions',
            'appearances',
        ]);

        return $model;
    }
}

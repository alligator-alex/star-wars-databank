<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Public\Services;

use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Common\Contracts\VehicleFilter;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

// TODO: cache
class VehicleService
{
    public const int PER_PAGE = 27;

    public function __construct(private readonly VehicleRepository $repository)
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
     * @param VehicleFilter|null $filter
     *
     * @return Builder
     */
    public function queryBuilder(?VehicleFilter $filter = null): Builder
    {
        /** @var Builder|Vehicle $query */
        $query = $this->repository->queryBuilder();

        $query->with([
            'image',
            'category',
            'type',
            'line',
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
     * @param VehicleFilter $filter
     *
     * @return LengthAwarePaginator
     */
    public function findPaginated(VehicleFilter $filter): LengthAwarePaginator
    {
        return $this->queryBuilder($filter)->paginate(static::PER_PAGE);
    }

    private function applyFilters(Builder $query, VehicleFilter $filter): void
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

        if ($filter->getCategories()) {
            $query->whereHas('category', fn (Builder $subQuery): Builder => $subQuery->whereIn(
                HandbookValue::tableName() . '.slug',
                $filter->getCategories()
            ));
        }

        if ($filter->getTypes()) {
            $query->whereHas('type', fn (Builder $subQuery): Builder => $subQuery->whereIn(
                HandbookValue::tableName() . '.slug',
                $filter->getTypes()
            ));
        }

        if ($filter->getLines()) {
            $query->whereHas('line', fn (Builder $subQuery): Builder => $subQuery->whereIn(
                HandbookValue::tableName() . '.slug',
                $filter->getLines()
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
     * Find Vehicle model by slug.
     *
     * @param string $slug
     * @param bool $withDrafts
     *
     * @return Vehicle|null
     */
    public function findOneBySlug(string $slug, bool $withDrafts = false): ?Vehicle
    {
        /** @var Vehicle|null $model */
        $model = $this->repository->findOneBySlug($slug, $withDrafts);

        if (!$model) {
            return null;
        }

        $model->load([
            'image',
            'category',
            'type',
            'line',
            'manufacturers',
            'mainFaction',
            'otherFactions',
            'appearances',
        ]);

        return $model;
    }
}

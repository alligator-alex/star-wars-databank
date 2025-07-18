<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Public\Services;

use App\Modules\Core\Common\Helpers\CacheHelper;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Common\Contracts\VehicleFilter;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;
use App\Modules\Vehicle\Public\Enums\CacheKeyPrefix;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VehicleService
{
    public const int PER_PAGE = 27;
    private const int RANDOM_COUNT_DEFAULT = 15;

    public function __construct(private readonly VehicleRepository $repository)
    {
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
     * @param int|null $page
     *
     * @return LengthAwarePaginator
     */
    public function findPaginated(VehicleFilter $filter, ?int $page = 1): LengthAwarePaginator
    {
        $cacheKey = CacheHelper::makePaginationKey(CacheKeyPrefix::PAGINATED, VehicleFilter::class, $filter, $page);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->queryBuilder($filter)->paginate(static::PER_PAGE);

        Cache::put($cacheKey, $result);

        return $result;
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

    public function findRandom(int $count = self::RANDOM_COUNT_DEFAULT): Collection
    {
        $cacheKey = CacheHelper::makeKey(CacheKeyPrefix::RANDOM, (string) $count);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->queryBuilder()
            ->reorder()
            ->inRandomOrder()
            ->limit($count)
            ->get();

        Cache::put($cacheKey, $result, 5 * 60);

        return $result;
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
        $cacheKey = CacheHelper::makeKey(CacheKeyPrefix::ONE, $slug, ($withDrafts ? 'draft' : null));
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        /** @var Vehicle|null $model */
        $model = $this->repository->findOneBySlug($slug, $withDrafts);

        $model?->load([
            'image',
            'category',
            'type',
            'line',
            'manufacturers',
            'mainFaction',
            'otherFactions',
            'appearances',
        ]);

        Cache::put($cacheKey, $model, 24 * 60 * 60);

        return $model;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Droid\Public\Services;

use App\Modules\Core\Common\Helpers\CacheHelper;
use App\Modules\Droid\Common\Contracts\DroidFilter;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Droid\Public\Enums\CacheKeyPrefix;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DroidService
{
    public const int PER_PAGE = 27;
    private const int RANDOM_COUNT_DEFAULT = 15;

    public function __construct(private readonly DroidRepository $repository)
    {
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
     * @param int|null $page
     *
     * @return LengthAwarePaginator
     */
    public function findPaginated(DroidFilter $filter, ?int $page = 1): LengthAwarePaginator
    {
        $cacheKey = CacheHelper::makePaginationKey(CacheKeyPrefix::PAGINATED, DroidFilter::class, $filter, $page);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->queryBuilder($filter)->paginate(static::PER_PAGE);

        Cache::put($cacheKey, $result);

        return $result;
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
     * Find Droid model by slug.
     *
     * @param string $slug
     * @param bool $withDrafts
     *
     * @return Droid|null
     */
    public function findOneBySlug(string $slug, bool $withDrafts = false): ?Droid
    {
        $cacheKey = CacheHelper::makeKey(CacheKeyPrefix::ONE, $slug, ($withDrafts ? 'draft' : null));
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        /** @var Droid|null $model */
        $model = $this->repository->findOneBySlug($slug, $withDrafts);

        $model?->load([
            'image',
            'line',
            'model',
            'class',
            'manufacturers',
            'mainFaction',
            'otherFactions',
            'appearances',
        ]);

        Cache::put($cacheKey, $model, 24 * 60 * 60);

        return $model;
    }
}

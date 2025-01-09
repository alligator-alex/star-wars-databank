<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Services;

use App\Modules\Databank\Common\Contracts\VehicleFilter;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Common\Enums\VehicleType;
use App\Modules\Databank\Common\Models\Faction;
use App\Modules\Databank\Common\Models\Line;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Models\Media;
use App\Modules\Databank\Common\Repositories\VehicleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

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
        return $this->repository->getQueryBuilder()->count();
    }

    /**
     * Get all models query.
     *
     * @param VehicleFilter|null $filter
     *
     * @return Builder
     */
    public function allQuery(?VehicleFilter $filter = null): Builder
    {
        /** @var Builder|Vehicle $query */
        $query = $this->repository->getQueryBuilder();

        $query->with([
            'image',
            'mainFaction',
        ]);

        if ($filter) {
            $this->applyFilters($query, $filter);
        }

        $query->orderBy('sort');
        $query->orderByDesc('id');

        return $query;
    }

    /**
     * Find all models with pagination.
     *
     * @param VehicleFilter $filter
     *
     * @return LengthAwarePaginator
     */
    public function findAllPaginated(VehicleFilter $filter): LengthAwarePaginator
    {
        return $this->allQuery($filter)->paginate(static::PER_PAGE);
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

        if ($filter->getLines()) {
            $query->whereHas(
                'line',
                fn (Builder $subQuery) => $subQuery->whereIn(Line::tableName() . '.slug', $filter->getLines())
            );
        }

        if ($filter->getCategories()) {
            $query->whereIn('category', $this->enumSlugsToValues(VehicleCategory::class, $filter->getCategories()));
        }

        if ($filter->getTypes()) {
            $query->whereIn('type', $this->enumSlugsToValues(VehicleType::class, $filter->getTypes()));
        }
    }

    /**
     * @param class-string $className
     * @param string[] $slugs
     *
     * @return int[]
     */
    private function enumSlugsToValues(string $className, array $slugs): array
    {
        $result = [];

        $cases = $className::cases();

        foreach ($slugs as $slug) {
            $enumName = Str::upper(Str::replace('-', '_', $slug));

            foreach ($cases as $enum) {
                if ($enum->name !== $enumName) {
                    continue;
                }

                $result[] = $enum->value;
                break;
            }
        }

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
    public function find(string $slug, bool $withDrafts = false): ?Vehicle
    {
        /** @var Vehicle|null $model */
        $model = $this->repository->findBySlug($slug, $withDrafts);

        if (!$model) {
            return null;
        }

        $model->load([
            'line',
            'image',
            'manufacturers',
            'mainFaction',
            'otherFactions',
            'appearances',
        ]);

        return $model;
    }
}

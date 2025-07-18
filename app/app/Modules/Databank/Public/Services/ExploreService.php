<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Services;

use App\Modules\Core\Common\Helpers\CacheHelper;
use App\Modules\Databank\Common\Contracts\Explorable;
use App\Modules\Databank\Public\Enums\CacheKeyPrefix;
use App\Modules\Databank\Public\Enums\ExploreRootType;
use App\Modules\Droid\Public\Services\DroidService;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Vehicle\Public\Services\VehicleService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ExploreService
{
    private const int ENTITIES_COUNT = 27;

    public function __construct(
        private readonly FactionRepository $factionRepository,
        private readonly MediaRepository $mediaRepository,
        private readonly VehicleService $vehicleService,
        private readonly DroidService $droidService
    ) {
    }

    public function findRootModel(ExploreRootType $type, string $slug): ?Explorable
    {
        $cacheKey = CacheHelper::makeKey(CacheKeyPrefix::EXPLORE, $type->value, 'slug', $slug);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $repository = match ($type) {
            ExploreRootType::FACTION => $this->factionRepository,
            ExploreRootType::MEDIA => $this->mediaRepository,
        };

        $model = $repository->findOneBySlug($slug);

        Cache::put($cacheKey, $model, 30 * 60);

        return $model;
    }

    public function findRelatedModels(Explorable $root): Collection
    {
        $type = match ($root->explorableType()) {
            ExploreRootType::FACTION => 'faction',
            ExploreRootType::MEDIA => 'media',
        };

        $relationName = match ($root->explorableType()) {
            ExploreRootType::FACTION => 'factions',
            ExploreRootType::MEDIA => 'appearances',
        };

        $cacheKey = CacheHelper::makeKey(CacheKeyPrefix::EXPLORE, $type, 'slug', $root->explorableKey(), 'related');
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $vehicles = $this->vehicleService->queryBuilder()
            ->whereHas(
                $relationName,
                fn (Builder $subQuery): Builder => $subQuery->where('slug', '=', $root->explorableKey())
            )
            ->inRandomOrder()
            ->limit((int) ceil(self::ENTITIES_COUNT / 2))
            ->get();

        $droids = $this->droidService->queryBuilder()
            ->whereHas(
                $relationName,
                fn (Builder $subQuery): Builder => $subQuery->where('slug', '=', $root->explorableKey())
            )
            ->inRandomOrder()
            ->limit(self::ENTITIES_COUNT - $vehicles->count())
            ->get();

        $result = $vehicles->concat($droids->all())->shuffle();

        Cache::put($cacheKey, $result, 30 * 60);

        return $result;
    }
}

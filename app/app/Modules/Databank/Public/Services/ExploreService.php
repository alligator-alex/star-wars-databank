<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Services;

use App\Modules\Droid\Public\Services\DroidService;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Faction\Public\Services\FactionService;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Public\Services\MediaService;
use App\Modules\Vehicle\Public\Services\VehicleService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

// TODO: cache
class ExploreService
{
    private const int ENTITIES_COUNT = 27;
    private const int RANDOM_ENTITIES_COUNT = 15;

    public function __construct(
        private readonly FactionService $factionService,
        private readonly MediaService $mediaService,
        private readonly VehicleService $vehicleService,
        private readonly DroidService $droidService,
    ) {
    }

    public function findRootModel(string $type, string $slug): Faction|Media|null
    {
        $service = match ($type) {
            'faction' => $this->factionService,
            'media' => $this->mediaService,
            default => null,
        };

        if ($service === null) {
            return null;
        }

        return $service->findOneBySlug($slug);
    }

    public function findRelatedModels(Faction|Media $root): Collection
    {
        $relationName = match ($root::class) {
            Faction::class => 'factions',
            Media::class => 'appearances',
            default => null,
        };

        if ($relationName === null) {
            return new Collection();
        }

        $vehicles = $this->vehicleService->queryBuilder()
            ->whereHas($relationName, fn (Builder $subQuery): Builder => $subQuery->where('slug', '=', $root->slug))
            ->inRandomOrder()
            ->limit((int) ceil(self::ENTITIES_COUNT / 2))
            ->get();

        $droids = $this->droidService->queryBuilder()
            ->whereHas($relationName, fn (Builder $subQuery): Builder => $subQuery->where('slug', '=', $root->slug))
            ->inRandomOrder()
            ->limit(self::ENTITIES_COUNT - $vehicles->count())
            ->get();

        return $vehicles->concat($droids->all())->shuffle();
    }

    public function findRandomVehicles(): Collection
    {
        return $this->vehicleService->findRandom(self::RANDOM_ENTITIES_COUNT);
    }

    public function findRandomDroids(): Collection
    {
        return $this->droidService->findRandom(self::RANDOM_ENTITIES_COUNT);
    }
}

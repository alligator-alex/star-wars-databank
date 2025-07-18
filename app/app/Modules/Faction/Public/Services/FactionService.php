<?php

declare(strict_types=1);

namespace App\Modules\Faction\Public\Services;

use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Faction\Public\Enums\CacheKeyPrefix;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FactionService
{
    public function __construct(private readonly FactionRepository $repository)
    {
    }

    /**
     * Find all models.
     *
     * @return Collection
     */
    public function findAll(): Collection
    {
        $cacheKey = CacheKeyPrefix::ALL->value;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->repository->queryBuilder()
            ->orderBy('sort')
            ->orderByDesc('id')
            ->get();

        Cache::put($cacheKey, $result, 24 * 60 * 60);

        return $result;
    }

    /**
     * @return array<string, string>
     */
    public function dropdownList(): array
    {
        $cacheKey = CacheKeyPrefix::DROPDOWN_LIST->value;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->repository->dropdownList(columnAsKey: 'slug');

        Cache::put($cacheKey, $result, 24 * 60 * 60);

        return $result;
    }
}

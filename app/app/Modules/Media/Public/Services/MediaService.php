<?php

declare(strict_types=1);

namespace App\Modules\Media\Public\Services;

use App\Modules\Media\Common\Enums\MediaType;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Media\Public\Enums\CacheKeyPrefix;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MediaService
{
    public function __construct(private readonly MediaRepository $repository)
    {
    }

    /**
     * @return Collection<int, MediaType>
     */
    public function availableTypes(): Collection
    {
        $cacheKey = CacheKeyPrefix::AVAILABLE_TYPES->value;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result =  $this->repository->queryBuilder()
            ->select('type')
            ->distinct()
            ->get()
            ->map(static fn (Media $media): MediaType => $media->type);

        Cache::put($cacheKey, $result, 24 * 60 * 60);

        return $result;
    }

    /**
     * Find all models.
     *
     * @return Collection<int, Media>
     */
    public function findAll(): Collection
    {
        $cacheKey = CacheKeyPrefix::ALL->value;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->repository->queryBuilder()
            ->with(['poster'])
            ->orderBy('sort')
            ->orderBy('release_date')
            ->orderBy('name')
            ->orderByDesc('id')
            ->get();

        Cache::put($cacheKey, $result, 24 * 60 * 60);

        return $result;
    }

    /**
     * @return array<string, array<int, string>>
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

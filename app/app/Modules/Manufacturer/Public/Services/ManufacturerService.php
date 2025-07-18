<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Public\Services;

use App\Modules\Manufacturer\Common\Repositories\ManufacturerRepository;
use App\Modules\Manufacturer\Public\Enums\CacheKeyPrefix;
use Illuminate\Support\Facades\Cache;

class ManufacturerService
{
    public function __construct(private readonly ManufacturerRepository $repository)
    {
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

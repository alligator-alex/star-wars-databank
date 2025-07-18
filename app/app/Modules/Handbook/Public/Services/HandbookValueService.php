<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Public\Services;

use App\Modules\Core\Common\Helpers\CacheHelper;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Handbook\Public\Enums\CacheKeyPrefix;
use Illuminate\Support\Facades\Cache;

class HandbookValueService
{
    public function __construct(private readonly HandbookValueRepository $repository)
    {
    }

    public function dropdownList(HandbookType $type): array
    {
        $cacheKey = CacheHelper::makeKey(CacheKeyPrefix::DROPDOWN_LIST, 'type', (string) $type->value);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->repository->dropdownList($type, 'slug');

        Cache::put($cacheKey, $result, 24 * 60 * 60);

        return $result;
    }
}

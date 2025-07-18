<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Helpers;

use App\Modules\Databank\Common\Contracts\Filterable;
use BackedEnum;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

class CacheHelper
{
    public static function forgetByWildcard(BackedEnum|string $prefix, string $wildcard = '*'): void
    {
        $store = Cache::getStore();
        if (!($store instanceof RedisStore)) {
            return;
        }

        $systemPrefix = config('database.redis.options.prefix');
        $pattern = $systemPrefix . self::makeKey($prefix, $wildcard);

        // Don't forget: iterator must be null for first call!
        $iterator = null;
        while ($keys = $store->connection()->client()->scan($iterator, $pattern, 100)) {
            foreach ($keys as $rawKey) {
                Cache::forget(str_replace($systemPrefix, '', $rawKey));
            }
        }
    }

    /**
     * @param BackedEnum|string $prefix
     * @param string ...$parts
     *
     * @return string
     */
    public static function makeKey(BackedEnum|string $prefix, ...$parts): string
    {
        $cacheKey = ($prefix instanceof BackedEnum) ? $prefix->value : $prefix;

        $parts = array_filter($parts);
        if (empty($parts)) {
            return $cacheKey;
        }

        return $cacheKey . ':' . implode(':', $parts);
    }

    /**
     * @param BackedEnum|string $prefix
     * @param class-string $filterContract
     * @param Filterable $filter
     * @param int $page
     *
     * @return string
     *
     * @throws ReflectionException
     */
    public static function makePaginationKey(
        BackedEnum|string $prefix,
        string $filterContract,
        Filterable $filter,
        int $page = 1,
    ): string {
        $parts = [];
        $filterParts = [];

        $filterReflection = new ReflectionClass($filterContract);
        foreach ($filterReflection->getMethods() as $method) {
            $data = $filter->{$method->getName()}();
            if (empty($data)) {
                continue;
            }

            $filterParts[] = Str::kebab(str_replace('get', '', $method->getName()));
            foreach ($data as $value) {
                $filterParts[] = $value;
            }
        }

        if (!empty($filterParts)) {
            $parts[] = 'filter-hash';
            // "xxh3" seems to be the fastest
            $parts[] = hash('xxh3', implode(':', $filterParts));
        }

        if ($page > 1) {
            $parts[] = 'page';
            $parts[] = $page;
        }

        return self::makeKey($prefix, ...$parts);
    }
}

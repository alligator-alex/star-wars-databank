<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Traits;

use Illuminate\Database\Eloquent\Builder;

trait AsDropdownList
{
    /**
     * @param bool $withDrafts
     * @param string $columnAsKey
     *
     * @return array<string, string>
     */
    public static function dropdownList(bool $withDrafts = false, string $columnAsKey = 'id'): array
    {
        static $listCache = null;

        if ($listCache === null) {
            $listCache = [];

            /** @var Builder|static $query */
            $query = static::query();

            if ($withDrafts) {
                $query->withDrafts();
            }

            $query->orderBy('name')->orderByDesc('id');

            /** @var static $item */
            foreach ($query->get() as $item) {
                $listCache[$item->{$columnAsKey}] = $item->name;
            }
        }

        return (array) $listCache;
    }
}

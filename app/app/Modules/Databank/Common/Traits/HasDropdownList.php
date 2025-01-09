<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasDropdownList
{
    /**
     * @param bool $withDrafts
     * @param string $columnAsKey
     *
     * @return array<string, string>
     */
    public static function dropdownList(bool $withDrafts = false, string $columnAsKey = 'id'): array
    {
        static $publicList = null;

        if (is_null($publicList)) {
            /** @var Builder|static $query */
            $query = static::query();

            if ($withDrafts) {
                $query->withDrafts();
            }

            $query->orderBy('name')->orderByDesc('id');

            /** @var static $item */
            foreach ($query->get() as $item) {
                $publicList[$item->{$columnAsKey}] = $item->name;
            }
        }

        return (array) $publicList;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Traits;

use App\Modules\Core\Common\Components\Model;

trait RepositoryWithDropdownList
{
    /**
     * @param bool $withDrafts
     * @param string $columnAsKey
     *
     * @return array<string, string>
     */
    public function dropdownList(bool $withDrafts = false, string $columnAsKey = 'id'): array
    {
        $query = $this->queryBuilder();

        if ($withDrafts) {
            /** @phpstan-ignore-next-line */
            $query->withDrafts();
        }

        $query->orderBy('name')->orderByDesc('id');

        $result = [];

        /** @var Model $item */
        foreach ($query->get() as $item) {
            /** @phpstan-ignore-next-line */
            $result[$item->{$columnAsKey}] = $item->name;
        }

        return $result;
    }
}

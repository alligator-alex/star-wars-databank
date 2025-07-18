<?php

declare(strict_types=1);

namespace App\Modules\Media\Common\Repositories;

use App\Modules\Databank\Common\Repositories\BaseRepository;
use App\Modules\Media\Common\Enums\MediaType;
use App\Modules\Media\Common\Models\Media;

/**
 * @extends BaseRepository<Media>
 */
class MediaRepository extends BaseRepository
{
    protected string $modelClass = Media::class;

    /**
     * @param bool $withDrafts
     * @param string $columnAsKey
     *
     * @return array<string, array<int, string>>
     */
    public function dropdownList(bool $withDrafts = false, string $columnAsKey = 'id'): array
    {
        $otherKey = __('Other');

        $list = [
            MediaType::MOVIE->nameForHumans() => [],
            MediaType::SERIES->nameForHumans() => [],
            MediaType::GAME->nameForHumans() => [],
            $otherKey => [],
        ];

        $query = $this->queryBuilder();

        if ($withDrafts) {
            /** @phpstan-ignore-next-line */
            $query->withDrafts();
        }

        $query->orderBy('sort')
            ->orderBy('release_date')
            ->orderBy('name')
            ->orderByDesc('id');

        /** @var Media $item */
        foreach ($query->get() as $item) {
            $value = $item->name;

            if ($item->release_date) {
                $value .= ' (' . $item->release_date->format('Y') . ')';
            }

            if (!$item->type) {
                $list[$otherKey][$item->{$columnAsKey}] = $value;
                continue;
            }

            $list[$item->type->nameForHumans()][$item->{$columnAsKey}] = $value;
        }

        foreach ($list as $type => $names) {
            if (empty($names)) {
                unset($list[$type]);
            }
        }

        return $list;
    }
}

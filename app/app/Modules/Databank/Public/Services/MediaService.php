<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Services;

use App\Modules\Databank\Common\Enums\MediaType;
use App\Modules\Databank\Common\Models\Media;
use App\Modules\Databank\Common\Repositories\MediaRepository;
use Illuminate\Support\Collection;

class MediaService
{
    public function __construct(private readonly MediaRepository $repository)
    {
    }

    /**
     * Find all models.
     *
     * @return array<string, Collection<Media>>
     */
    public function findAll(): array
    {
        $otherKey = __('Other');

        $result = [
            MediaType::MOVIE->nameForHumans() => new Collection(),
            MediaType::SERIES->nameForHumans() => new Collection(),
            MediaType::GAME->nameForHumans() => new Collection(),
            $otherKey => new Collection(),
        ];

        $query = $this->repository->getQueryBuilder()
            ->with(['poster'])
            ->orderBy('sort')
            ->orderBy('release_date')
            ->orderBy('name')
            ->orderByDesc('id');

        /** @var Media $media */
        foreach ($query->cursor() as $media) {
            $key = $media->type?->nameForHumans() ?? $otherKey;

            $result[$key]->add($media);
        }

        return $result;
    }
}

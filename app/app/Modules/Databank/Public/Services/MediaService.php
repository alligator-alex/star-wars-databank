<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Services;

use App\Modules\Databank\Common\Enums\MediaType;
use App\Modules\Databank\Common\Models\Media;
use App\Modules\Databank\Common\Repositories\MediaRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MediaService
{
    public function __construct(private readonly MediaRepository $repository)
    {
    }

    /**
     * @return Collection<MediaType>
     */
    public function findAvailableTypes(): Collection
    {
        return $this->repository->getQueryBuilder()
            ->select('type')
            ->distinct()
            ->get()
            ->map(static fn (Media $media) => $media->type);
    }

    /**
     * Find all models.
     *
     * @return Collection<Media>
     */
    public function findAll(): Collection
    {
        return $this->repository->getQueryBuilder()
            ->with(['poster'])
            ->orderBy('sort')
            ->orderBy('release_date')
            ->orderBy('name')
            ->orderByDesc('id')
            ->get();
    }
}

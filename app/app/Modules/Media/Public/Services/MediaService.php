<?php

declare(strict_types=1);

namespace App\Modules\Media\Public\Services;

use App\Modules\Media\Common\Enums\MediaType;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Common\Repositories\MediaRepository;
use Illuminate\Support\Collection;

// TODO: cache
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
        return $this->repository->queryBuilder()
            ->select('type')
            ->distinct()
            ->get()
            ->map(static fn (Media $media): MediaType => $media->type);
    }

    /**
     * Find all models.
     *
     * @return Collection<int, Media>
     */
    public function findAll(): Collection
    {
        return $this->repository->queryBuilder()
            ->with(['poster'])
            ->orderBy('sort')
            ->orderBy('release_date')
            ->orderBy('name')
            ->orderByDesc('id')
            ->get();
    }

    public function findOneBySlug(string $slug): ?Media
    {
        /** @var Media|null $model */
        $model = $this->repository->findOneBySlug($slug);

        return $model;
    }
}

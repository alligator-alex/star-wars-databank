<?php

declare(strict_types=1);

namespace App\Modules\Faction\Public\Services;

use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use Illuminate\Support\Collection;

// TODO: cache
class FactionService
{
    public function __construct(private readonly FactionRepository $repository)
    {
    }

    /**
     * Find all models.
     *
     * @return Collection
     */
    public function findAll(): Collection
    {
        return $this->repository->queryBuilder()
            ->orderBy('sort')
            ->orderByDesc('id')
            ->get();
    }

    public function findOneBySlug(string $slug): ?Faction
    {
        /** @var Faction|null $model */
        $model = $this->repository->findOneBySlug($slug);

        return $model;
    }
}

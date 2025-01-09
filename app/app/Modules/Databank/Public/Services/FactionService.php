<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Services;

use App\Modules\Databank\Common\Repositories\FactionRepository;
use Illuminate\Support\Collection;

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
        return $this->repository->getQueryBuilder()
            ->orderBy('sort')
            ->orderByDesc('id')
            ->get();
    }
}

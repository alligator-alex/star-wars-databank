<?php

declare(strict_types=1);

namespace App\Modules\Sitemap\Common\Providers;

use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Sitemap\Common\Contracts\Sitemappable;
use App\Modules\Sitemap\Common\Enums\ChangeFrequency;
use Illuminate\Support\LazyCollection;

class DroidProvider extends SitemapProvider
{
    public function __construct(private readonly DroidRepository $repository)
    {
    }

    public function getCode(): string
    {
        return 'droids';
    }

    /**
     * @inheritDoc
     */
    public function getItems(): array
    {
        /** @var LazyCollection<int, Sitemappable> $models */
        $models = $this->repository->queryBuilder()
            ->orderBy('name')
            ->orderBy('sort')
            ->orderByDesc('id')
            ->lazy();

        return $this->createSitemapItemsFromModels($models, 0.8, ChangeFrequency::MONTHLY);
    }
}

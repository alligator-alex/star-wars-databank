<?php

declare(strict_types=1);

namespace App\Modules\Sitemap\Common\Providers;

use App\Modules\Sitemap\Common\Contracts\Sitemappable;
use App\Modules\Sitemap\Common\DTOs\SitemapItem;
use App\Modules\Sitemap\Common\Enums\ChangeFrequency;

abstract class SitemapProvider
{
    abstract public function getCode(): string;

    /**
     * @return SitemapItem[]
     */
    abstract public function getItems(): array;

    /**
     * @param Sitemappable[] $models
     * @param float $priority
     * @param ChangeFrequency|null $changefreq
     *
     * @return array<int, SitemapItem>
     */
    protected function createSitemapItemsFromModels(
        iterable $models,
        float $priority,
        ?ChangeFrequency $changefreq = null
    ): array {
        $items = [];

        foreach ($models as $model) {
            $items[] = new SitemapItem(
                $model->getSitemapUrl(),
                $model->getSitemapModificationDate(),
                $changefreq,
                $priority
            );
        }

        return $items;
    }
}

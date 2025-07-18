<?php

declare(strict_types=1);

namespace App\Modules\Sitemap\Common\Providers;

use App\Modules\Databank\Public\Enums\DatabankRouteName;
use App\Modules\Databank\Public\Enums\ExploreRootType;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Sitemap\Common\DTOs\SitemapItem;
use App\Modules\Sitemap\Common\Enums\ChangeFrequency;
use Illuminate\Support\Carbon;

class StaticProvider extends SitemapProvider
{
    public function __construct(
        private readonly FactionRepository $factionRepository,
        private readonly MediaRepository $mediaRepository
    ) {
    }

    public function getCode(): string
    {
        return 'static';
    }

    public function getItems(): array
    {
        $items = [
            new SitemapItem(route(DatabankRouteName::HOME)),
        ];

        $this->factionRepository->queryBuilder()
            ->lazyById()
            ->each(static function (Faction $faction) use (&$items): void {
                $items[] = new SitemapItem(
                    route(DatabankRouteName::EXPLORE, [
                        'type' => ExploreRootType::FACTION->value,
                        'slug' => $faction->slug,
                    ]),
                    Carbon::now(),
                    ChangeFrequency::HOURLY,
                    0.5
                );
            });

        $this->mediaRepository->queryBuilder()
            ->lazyById()
            ->each(static function (Media $media) use (&$items): void {
                $items[] = new SitemapItem(
                    route(DatabankRouteName::EXPLORE, [
                        'type' => ExploreRootType::MEDIA->value,
                        'slug' => $media->slug,
                    ]),
                    Carbon::now(),
                    ChangeFrequency::HOURLY,
                    0.5
                );
            });

        return $items;
    }
}

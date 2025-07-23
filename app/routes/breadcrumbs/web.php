<?php

declare(strict_types=1);

use App\Modules\Databank\Public\Enums\DatabankRouteName;
use App\Modules\Databank\Public\Enums\ExploreRootType;
use App\Modules\Databank\Public\Services\ExploreService;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use App\Modules\Droid\Public\Services\DroidService;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;
use App\Modules\Vehicle\Public\Services\VehicleService;
use Tabuna\Breadcrumbs\Breadcrumbs;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Public Breadcrumbs
|--------------------------------------------------------------------------
*/

// Databank
Breadcrumbs::for(
    DatabankRouteName::HOME->value,
    static fn (Trail $trail) => $trail
        ->push(__('Databank'), route(name: DatabankRouteName::HOME, absolute: false))
);

// Explore
Breadcrumbs::for(
    DatabankRouteName::EXPLORE->value,
    static function (Trail $trail, string $type, string $slug): void {
        /** @var ExploreService $service */
        $service = app()->make(ExploreService::class);

        /** @var ExploreRootType $typeEnum */
        $typeEnum = ExploreRootType::tryFrom($type);

        /** @var Faction|Media $root */
        $root = $service->findRootModel($typeEnum, $slug);

        $trail->parent(DatabankRouteName::HOME->value)
            ->push(__('Explore'), route(DatabankRouteName::EXPLORE, ['type' => $type, 'slug' => $slug], false))
            ->push(mb_ucfirst($type), route(DatabankRouteName::EXPLORE, ['type' => $type, 'slug' => $slug], false))
            ->push($root->name);
    }
);

// Vehicles
Breadcrumbs::for(
    VehicleRouteName::INDEX->value,
    static fn (Trail $trail): Trail => $trail
        ->parent(DatabankRouteName::HOME->value)
        ->push(__('Vehicles'), route(name: VehicleRouteName::INDEX, absolute: false))
);

Breadcrumbs::for(
    VehicleRouteName::DETAIL->value,
    static function (Trail $trail, string $slug): void {
        /** @var VehicleService $service */
        $service = app()->make(VehicleService::class);

        $trail->parent(VehicleRouteName::INDEX->value)
            ->push(
                $service->findOneBySlug($slug)?->name ?? __('Error'),
                route(VehicleRouteName::DETAIL, ['slug' => $slug], false)
            );
    }
);

// Droids
Breadcrumbs::for(
    DroidRouteName::INDEX->value,
    static fn (Trail $trail): Trail => $trail
        ->parent(DatabankRouteName::HOME->value)
        ->push(__('Droids'), route(name: DroidRouteName::INDEX, absolute: false))
);

Breadcrumbs::for(
    DroidRouteName::DETAIL->value,
    static function (Trail $trail, string $slug): void {
        /** @var DroidService $service */
        $service = app()->make(DroidService::class);

        $trail->parent(DroidRouteName::INDEX->value)
            ->push(
                $service->findOneBySlug($slug)?->name ?? __('Error'),
                route(DroidRouteName::DETAIL, ['slug' => $slug], false)
            );
    }
);

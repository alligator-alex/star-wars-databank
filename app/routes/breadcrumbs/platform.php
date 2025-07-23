<?php

declare(strict_types=1);

use App\Modules\Core\Admin\Enums\AdminRouteName;
use App\Modules\Core\Admin\Enums\UserRouteName;
use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Droid\Admin\Services\DroidService;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Faction\Admin\Services\FactionService;
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Manufacturer\Admin\Enums\ManufacturerRouteName;
use App\Modules\Manufacturer\Admin\Services\ManufacturerService;
use App\Modules\Media\Admin\Enums\MediaRouteName;
use App\Modules\Media\Admin\Services\MediaService;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Admin\Services\VehicleService;
use Tabuna\Breadcrumbs\Breadcrumbs;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Admin Breadcrumbs
|--------------------------------------------------------------------------
*/

// Home
Breadcrumbs::for(
    AdminRouteName::HOME->value,
    static fn (Trail $trail): Trail => $trail
        ->push(__('Dashboard'), route(name: AdminRouteName::HOME, absolute: false))
);

// Profile
Breadcrumbs::for(
    UserRouteName::PROFILE->value,
    static fn (Trail $trail): Trail => $trail
        ->push(__('Profile'), route(name: UserRouteName::PROFILE, absolute: false))
);

// Handbooks
Breadcrumbs::for(
    HandbookValueRouteName::INDEX->value,
    static function (Trail $trail, int $handbookId): void {
        /** @var HandbookRepository $handbookRepository */
        $handbookRepository = app()->make(HandbookRepository::class);

        $trail->push(__('Handbooks'))
            ->push(
                $handbookRepository->findOneById($handbookId)?->name ?? __('Values'),
                route(HandbookValueRouteName::INDEX, ['handbookId' => $handbookId])
            );
    }
);

Breadcrumbs::for(
    HandbookValueRouteName::CREATE->value,
    static fn (Trail $trail, int $handbookId): Trail => $trail
        ->parent(HandbookValueRouteName::INDEX->value, $handbookId)
        ->push(__('Create'))
);

Breadcrumbs::for(
    HandbookValueRouteName::EDIT->value,
    static function (Trail $trail, int $handbookId, int $handbookValueId): void {
        /** @var HandbookValueRepository $service */
        $repository = app()->make(HandbookValueRepository::class);

        $trail->parent(HandbookValueRouteName::INDEX->value, $handbookId)
            ->push(
                $repository->findOneById($handbookValueId)?->name ?? __('Unknown'),
                route(HandbookValueRouteName::EDIT->value, [
                    'handbookId' => $handbookId,
                    'handbookValueId' => $handbookValueId,
                ], false)
            )
            ->push(__('Edit'));
    }
);

// Factions
Breadcrumbs::for(
    FactionRouteName::INDEX->value,
    static fn (Trail $trail): Trail => $trail
        ->push(__('Factions'), ManufacturerRouteName::INDEX->value)
);

Breadcrumbs::for(
    FactionRouteName::CREATE->value,
    static fn (Trail $trail): Trail => $trail
        ->parent(FactionRouteName::INDEX->value)
        ->push(__('Create'))
);

Breadcrumbs::for(
    FactionRouteName::EDIT->value,
    static function (Trail $trail, int $id): void {
        /** @var FactionService $service */
        $service = app()->make(FactionService::class);

        $trail->parent(FactionRouteName::INDEX->value)
            ->push(
                $service->findOneById($id)?->name ?? __('Unknown'),
                route(FactionRouteName::EDIT->value, ['id' => $id], false)
            )
            ->push(__('Edit'));
    }
);

// Manufacturers
Breadcrumbs::for(
    ManufacturerRouteName::INDEX->value,
    static fn (Trail $trail): Trail => $trail
        ->push(__('Manufacturers'), ManufacturerRouteName::INDEX->value)
);

Breadcrumbs::for(
    ManufacturerRouteName::CREATE->value,
    static fn (Trail $trail): Trail => $trail
        ->parent(ManufacturerRouteName::INDEX->value)
        ->push(__('Create'))
);

Breadcrumbs::for(
    ManufacturerRouteName::EDIT->value,
    static function (Trail $trail, int $id): void {
        /** @var ManufacturerService $service */
        $service = app()->make(ManufacturerService::class);

        $trail->parent(ManufacturerRouteName::INDEX->value)
            ->push(
                $service->findOneById($id)?->name ?? __('Unknown'),
                route(ManufacturerRouteName::EDIT->value, ['id' => $id], false)
            )
            ->push(__('Edit'));
    }
);

// Media
Breadcrumbs::for(
    MediaRouteName::INDEX->value,
    static fn (Trail $trail): Trail => $trail
        ->push(__('Media'), MediaRouteName::INDEX->value)
);

Breadcrumbs::for(
    MediaRouteName::CREATE->value,
    static fn (Trail $trail): Trail => $trail
        ->parent(MediaRouteName::INDEX->value)
        ->push(__('Create'))
);

Breadcrumbs::for(
    MediaRouteName::EDIT->value,
    static function (Trail $trail, int $id): void {
        /** @var MediaService $service */
        $service = app()->make(MediaService::class);

        $trail->parent(MediaRouteName::INDEX->value)
            ->push(
                $service->findOneById($id)?->name ?? __('Unknown'),
                route(MediaRouteName::EDIT->value, ['id' => $id], false)
            )
            ->push(__('Edit'));
    }
);

// Vehicles
Breadcrumbs::for(
    VehicleRouteName::INDEX->value,
    static fn (Trail $trail): Trail => $trail
        ->push(__('Vehicles'), VehicleRouteName::INDEX->value)
);

Breadcrumbs::for(
    VehicleRouteName::CREATE->value,
    static fn (Trail $trail): Trail => $trail
        ->parent(VehicleRouteName::INDEX->value)
        ->push(__('Create'))
);

Breadcrumbs::for(
    VehicleRouteName::EDIT->value,
    static function (Trail $trail, int $id): void {
        /** @var VehicleService $service */
        $service = app()->make(VehicleService::class);

        $trail->parent(VehicleRouteName::INDEX->value)
            ->push(
                $service->findOneById($id)?->name ?? __('Unknown'),
                route(VehicleRouteName::EDIT->value, ['id' => $id], false)
            )
            ->push(__('Edit'));
    }
);

// Droids
Breadcrumbs::for(
    DroidRouteName::INDEX->value,
    static fn (Trail $trail): Trail => $trail
        ->push(__('Droids'), DroidRouteName::INDEX->value)
);

Breadcrumbs::for(
    DroidRouteName::CREATE->value,
    static fn (Trail $trail): Trail => $trail
        ->parent(DroidRouteName::INDEX->value)
        ->push(__('Create'))
);

Breadcrumbs::for(
    DroidRouteName::EDIT->value,
    static function (Trail $trail, int $id): void {
        /** @var DroidService $service */
        $service = app()->make(DroidService::class);

        $trail->parent(DroidRouteName::INDEX->value)
            ->push(
                $service->findOneById($id)?->name ?? __('Unknown'),
                route(DroidRouteName::EDIT->value, ['id' => $id], false)
            )
            ->push(__('Edit'));
    }
);

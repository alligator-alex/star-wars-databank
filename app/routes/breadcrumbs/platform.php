<?php

declare(strict_types=1);

use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Manufacturer\Admin\Enums\ManufacturerRouteName;
use App\Modules\Manufacturer\Common\Repositories\ManufacturerRepository;
use App\Modules\Media\Admin\Enums\MediaRouteName;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;
use Tabuna\Breadcrumbs\Breadcrumbs;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Admin Breadcrumbs
|--------------------------------------------------------------------------
*/

// Handbooks
/** @var HandbookRepository $handbookRepository */
$handbookRepository = app()->make(HandbookRepository::class);

/** @var HandbookValueRepository $handbookValueRepository */
$handbookValueRepository = app()->make(HandbookValueRepository::class);

Breadcrumbs::for(
    HandbookValueRouteName::INDEX->value,
    static fn (Trail $trail, int $handbookId): Trail =>
    $trail->push(__('Handbooks'))
        ->push(
            $handbookRepository->findOneById($handbookId)?->name ?? __('Values'),
            route(HandbookValueRouteName::INDEX, ['handbookId' => $handbookId])
        )
);

Breadcrumbs::for(
    HandbookValueRouteName::CREATE->value,
    static fn (Trail $trail, int $handbookId): Trail =>
    $trail->parent(HandbookValueRouteName::INDEX->value, $handbookId)
        ->push(__('Create'))
);

Breadcrumbs::for(
    HandbookValueRouteName::EDIT->value,
    static fn (Trail $trail, int $handbookId, int $handbookValueId): Trail =>
    $trail->parent(HandbookValueRouteName::INDEX->value, $handbookId)
        ->push(
            $handbookValueRepository->findOneById($handbookValueId)?->name,
            route(HandbookValueRouteName::EDIT->value, [
                'handbookId' => $handbookId,
                'handbookValueId' => $handbookValueId,
            ], false)
        )
        ->push(__('Edit'))
);

// Factions
/** @var FactionRepository $factionRepository */
$factionRepository = app()->make(FactionRepository::class);

Breadcrumbs::for(
    FactionRouteName::INDEX->value,
    static fn (Trail $trail): Trail =>
    $trail->push(__('Factions'), ManufacturerRouteName::INDEX->value)
);

Breadcrumbs::for(
    FactionRouteName::CREATE->value,
    static fn (Trail $trail): Trail =>
    $trail->parent(FactionRouteName::INDEX->value)
        ->push(__('Create'))
);

Breadcrumbs::for(
    FactionRouteName::EDIT->value,
    static fn (Trail $trail, int $id): Trail =>
    $trail->parent(FactionRouteName::INDEX->value)
        ->push(
            $factionRepository->findOneById($id, true)?->name,
            route(FactionRouteName::EDIT->value, ['id' => $id], false)
        )
        ->push(__('Edit'))
);

// Manufacturers
/** @var ManufacturerRepository $manufacturerRepository */
$manufacturerRepository = app()->make(ManufacturerRepository::class);

Breadcrumbs::for(
    ManufacturerRouteName::INDEX->value,
    static fn (Trail $trail): Trail =>
    $trail->push(__('Manufacturers'), ManufacturerRouteName::INDEX->value)
);

Breadcrumbs::for(
    ManufacturerRouteName::CREATE->value,
    static fn (Trail $trail): Trail =>
    $trail->parent(ManufacturerRouteName::INDEX->value)
        ->push(__('Create'))
);

Breadcrumbs::for(
    ManufacturerRouteName::EDIT->value,
    static fn (Trail $trail, int $id): Trail =>
    $trail->parent(ManufacturerRouteName::INDEX->value)
        ->push(
            $manufacturerRepository->findOneById($id, true)?->name,
            route(ManufacturerRouteName::EDIT->value, ['id' => $id], false)
        )
        ->push(__('Edit'))
);

// Media
/** @var ManufacturerRepository $mediaRepository */
$mediaRepository = app()->make(MediaRepository::class);

Breadcrumbs::for(
    MediaRouteName::INDEX->value,
    static fn (Trail $trail): Trail =>
    $trail->push(__('Media'), MediaRouteName::INDEX->value)
);

Breadcrumbs::for(
    MediaRouteName::CREATE->value,
    static fn (Trail $trail): Trail =>
    $trail->parent(MediaRouteName::INDEX->value)
        ->push(__('Create'))
);

Breadcrumbs::for(
    MediaRouteName::EDIT->value,
    static fn (Trail $trail, int $id): Trail =>
    $trail->parent(MediaRouteName::INDEX->value)
        ->push(
            $mediaRepository->findOneById($id, true)?->name,
            route(MediaRouteName::EDIT->value, ['id' => $id], false)
        )
        ->push(__('Edit'))
);

// Vehicles
/** @var VehicleRepository $vehicleRepository */
$vehicleRepository = app()->make(VehicleRepository::class);

Breadcrumbs::for(
    VehicleRouteName::INDEX->value,
    static fn (Trail $trail): Trail =>
    $trail->push(__('Vehicles'), VehicleRouteName::INDEX->value)
);

Breadcrumbs::for(
    VehicleRouteName::CREATE->value,
    static fn (Trail $trail): Trail =>
    $trail->parent(VehicleRouteName::INDEX->value)
        ->push(__('Create'))
);

Breadcrumbs::for(
    VehicleRouteName::EDIT->value,
    static fn (Trail $trail, int $id): Trail =>
    $trail->parent(VehicleRouteName::INDEX->value)
        ->push(
            $vehicleRepository->findOneById($id, true)?->name,
            route(VehicleRouteName::EDIT->value, ['id' => $id], false)
        )
        ->push(__('Edit'))
);

// Droids
/** @var DroidRepository $droidRepository */
$droidRepository = app()->make(DroidRepository::class);

Breadcrumbs::for(
    DroidRouteName::INDEX->value,
    static fn (Trail $trail): Trail =>
    $trail->push(__('Droids'), DroidRouteName::INDEX->value)
);

Breadcrumbs::for(
    DroidRouteName::CREATE->value,
    static fn (Trail $trail): Trail =>
    $trail->parent(DroidRouteName::INDEX->value)
        ->push(__('Create'))
);

Breadcrumbs::for(
    DroidRouteName::EDIT->value,
    static fn (Trail $trail, int $id): Trail =>
    $trail->parent(DroidRouteName::INDEX->value)
        ->push(
            $droidRepository->findOneById($id, true)?->name,
            route(DroidRouteName::EDIT->value, ['id' => $id], false)
        )
        ->push(__('Edit'))
);

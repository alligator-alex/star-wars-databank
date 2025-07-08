<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Controllers;

use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Manufacturer\Admin\Enums\ManufacturerRouteName;
use App\Modules\Manufacturer\Common\Repositories\ManufacturerRepository;
use App\Modules\Media\Admin\Enums\MediaRouteName;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class HomePageController extends Screen
{
    public function __construct(
        private readonly FactionRepository $factionRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly MediaRepository $mediaRepository,
        private readonly HandbookRepository $handbookRepository,
        private readonly HandbookValueRepository $handbookValueRepository,
        private readonly VehicleRepository $vehicleRepository,
        private readonly DroidRepository $droidRepository
    ) {
    }

    /**
     * @return array<int, null>
     */
    public function query(): iterable
    {
        return [];
    }

    public function name(): ?string
    {
        return 'Hello there';
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('platform::partials.update-assets'),

            Layout::rows([
                Group::make([
                    Link::make(__('Manage Vehicles (:count)', [
                        'count' => $this->vehicleRepository->count(true),
                    ]))
                    ->route(VehicleRouteName::INDEX->value, absolute: false)
                    ->type(Color::PRIMARY),

                    Link::make(__('Manage Categories (:count)', [
                        'count' => $this->handbookValueRepository->count(HandbookType::VEHICLE_CATEGORY),
                    ]))
                    ->route(HandbookValueRouteName::INDEX->value, [
                        'handbookId' => $this->handbookRepository->findOneByType(HandbookType::VEHICLE_CATEGORY)?->id,
                    ], false),

                    Link::make(__('Manage Types (:count)', [
                        'count' => $this->handbookValueRepository->count(HandbookType::VEHICLE_TYPE),
                    ]))
                    ->route(HandbookValueRouteName::INDEX->value, [
                        'handbookId' => $this->handbookRepository->findOneByType(HandbookType::VEHICLE_TYPE)?->id,
                    ], false),

                    Link::make(__('Manage Lines (:count)', [
                        'count' => $this->handbookValueRepository->count(HandbookType::VEHICLE_LINE),
                    ]))
                    ->route(HandbookValueRouteName::INDEX->value, [
                        'handbookId' => $this->handbookRepository->findOneByType(HandbookType::VEHICLE_LINE)?->id,
                    ], false),
                ])->autoWidth(),
            ])->title(__('Vehicles')),

            Layout::rows([
                Group::make([
                    Link::make(__('Manage Droids (:count)', [
                        'count' => $this->droidRepository->count(true)
                    ]))
                    ->route(DroidRouteName::INDEX->value, absolute: false)
                    ->type(Color::PRIMARY),

                    Link::make(__('Manage Lines (:count)', [
                        'count' => $this->handbookValueRepository->count(HandbookType::DROID_LINE),
                    ]))
                    ->route(HandbookValueRouteName::INDEX->value, [
                        'handbookId' => $this->handbookRepository->findOneByType(HandbookType::DROID_LINE)?->id,
                    ], false),

                    Link::make(__('Manage Models (:count)', [
                        'count' => $this->handbookValueRepository->count(HandbookType::DROID_MODEL),
                    ]))
                    ->route(HandbookValueRouteName::INDEX->value, [
                        'handbookId' => $this->handbookRepository->findOneByType(HandbookType::DROID_MODEL)?->id,
                    ], false),

                    Link::make(__('Manage Classes (:count)', [
                        'count' => $this->handbookValueRepository->count(HandbookType::DROID_CLASS),
                    ]))
                    ->route(HandbookValueRouteName::INDEX->value, [
                        'handbookId' => $this->handbookRepository->findOneByType(HandbookType::DROID_CLASS)?->id,
                    ], false),
                ])->autoWidth(),
            ])->title(__('Droids')),

            Layout::rows([
                Group::make([
                    Link::make(__('Manage Factions (:count)', [
                        'count' => $this->factionRepository->count(true)
                    ]))
                    ->route(FactionRouteName::INDEX->value, absolute: false)
                    ->type(Color::DEFAULT),

                    Link::make(__('Manage Manufacturers (:count)', [
                        'count' => $this->manufacturerRepository->count(true)
                    ]))
                    ->route(ManufacturerRouteName::INDEX->value, absolute: false)
                    ->type(Color::DEFAULT),

                    Link::make(__('Manage Media (:count)', [
                        'count' => $this->mediaRepository->count(true)
                    ]))
                    ->route(MediaRouteName::INDEX->value, absolute: false)
                    ->type(Color::DEFAULT),
                ])->autoWidth(),
            ])->title(__('Misc')),
        ];
    }
}

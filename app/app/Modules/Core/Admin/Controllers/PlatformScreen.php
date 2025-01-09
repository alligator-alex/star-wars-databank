<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Controllers;

use App\Modules\Databank\Admin\Enums\FactionRouteName;
use App\Modules\Databank\Admin\Enums\LineRouteName;
use App\Modules\Databank\Admin\Enums\ManufacturerRouteName;
use App\Modules\Databank\Admin\Enums\VehicleRouteName;
use App\Modules\Databank\Admin\Enums\MediaRouteName;
use App\Modules\Databank\Common\Repositories\FactionRepository;
use App\Modules\Databank\Common\Repositories\LineRepository;
use App\Modules\Databank\Common\Repositories\ManufacturerRepository;
use App\Modules\Databank\Common\Repositories\VehicleRepository;
use App\Modules\Databank\Common\Repositories\MediaRepository;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    public function __construct(
        private readonly VehicleRepository $vehicleRepository,
        private readonly FactionRepository $factionRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly LineRepository $lineRepository,
        private readonly MediaRepository $mediaRepository
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
                        'count' => $this->vehicleRepository->getQueryBuilder()->count()
                    ]))->route(VehicleRouteName::LIST->value)->type(Color::PRIMARY),

                    Link::make(__('Manage Factions (:count)', [
                        'count' => $this->factionRepository->getQueryBuilder()->count()
                    ]))->route(FactionRouteName::LIST->value)->type(Color::INFO),

                    Link::make(__('Manage Manufacturers (:count)', [
                        'count' => $this->manufacturerRepository->getQueryBuilder()->count()
                    ]))->route(ManufacturerRouteName::LIST->value)->type(Color::DEFAULT),

                    Link::make(__('Manage Lines (:count)', [
                        'count' => $this->lineRepository->getQueryBuilder()->count()
                    ]))->route(LineRouteName::LIST->value)->type(Color::DEFAULT),

                    Link::make(__('Manage Media (:count)', [
                        'count' => $this->mediaRepository->getQueryBuilder()->count()
                    ]))->route(MediaRouteName::LIST->value)->type(Color::SUCCESS),
                ])->autoWidth(),
            ]),
        ];
    }
}

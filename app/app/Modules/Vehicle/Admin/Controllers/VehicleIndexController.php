<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Controllers;

use App\Modules\Vehicle\Admin\Components\Layouts\Index\FilterLayout;
use App\Modules\Vehicle\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Admin\Services\VehicleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;

class VehicleIndexController extends Screen
{
    public function __construct(private readonly VehicleService $service)
    {
    }

    public function name(): string
    {
        return __('Vehicles');
    }

    /**
     * Command bar.
     *
     * @return array<int, Action>
     */
    public function commandBar(): array
    {
        return [
            Link::make(__('New vehicle'))
                ->route(name: VehicleRouteName::CREATE->value, absolute: false)
                ->icon('plus-circle')
                ->class('btn icon-link rounded')
                ->type(Color::PRIMARY),
        ];
    }

    /**
     * @return array<string, LengthAwarePaginator>
     */
    public function query(FilterLayout $filter): array
    {
        return [
            'items' => $this->service->findPaginated($filter),
        ];
    }

    public function layout(): iterable
    {
        return [
            new FilterLayout(),
            new IndexLayout(),
        ];
    }
}

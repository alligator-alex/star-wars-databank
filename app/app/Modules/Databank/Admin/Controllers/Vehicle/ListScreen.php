<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Controllers\Vehicle;

use App\Modules\Databank\Admin\Components\Vehicle\Layouts\List\FilterSelection;
use App\Modules\Databank\Admin\Components\Vehicle\Layouts\List\ListTable;
use App\Modules\Databank\Admin\Enums\VehicleRouteName;
use App\Modules\Databank\Admin\Services\VehicleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;

class ListScreen extends Screen
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
            Link::make(__('Add'))
                ->route(name: VehicleRouteName::NEW->value, absolute: false)
                ->icon('plus-circle')
                ->class('btn icon-link rounded')
                ->type(Color::PRIMARY),
        ];
    }

    /**
     * @return array<string, LengthAwarePaginator>
     */
    public function query(FilterSelection $filter): array
    {
        return [
            'items' => $this->service->findAllPaginated($filter),
        ];
    }

    public function layout(): iterable
    {
        return [
            new FilterSelection(),
            new ListTable(),
        ];
    }
}

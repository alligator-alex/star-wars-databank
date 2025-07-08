<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Admin\Controllers;

use App\Modules\Manufacturer\Admin\Components\Layouts\Index\FilterLayout;
use App\Modules\Manufacturer\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Manufacturer\Admin\Enums\ManufacturerRouteName;
use App\Modules\Manufacturer\Admin\Services\ManufacturerService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;

class ManufacturerIndexController extends Screen
{
    public function __construct(private readonly ManufacturerService $service)
    {
    }

    public function name(): string
    {
        return __('Manufacturers');
    }

    /**
     * Command bar.
     *
     * @return array<int, Action>
     */
    public function commandBar(): array
    {
        return [
            Link::make(__('New manufacturer'))
                ->route(ManufacturerRouteName::CREATE->value)
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

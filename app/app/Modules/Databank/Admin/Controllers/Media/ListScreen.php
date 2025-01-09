<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Controllers\Media;

use App\Modules\Databank\Admin\Components\Media\Layouts\List\FilterSelection;
use App\Modules\Databank\Admin\Components\Media\Layouts\List\ListTable;
use App\Modules\Databank\Admin\Enums\MediaRouteName;
use App\Modules\Databank\Admin\Services\MediaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;

class ListScreen extends Screen
{
    public function __construct(private readonly MediaService $service)
    {
    }

    public function name(): string
    {
        return __('Media');
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
                ->route(MediaRouteName::NEW->value)
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

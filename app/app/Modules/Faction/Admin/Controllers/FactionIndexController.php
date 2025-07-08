<?php

declare(strict_types=1);

namespace App\Modules\Faction\Admin\Controllers;

use App\Modules\Faction\Admin\Components\Layouts\Index\FilterLayout;
use App\Modules\Faction\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Faction\Admin\Services\FactionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;

class FactionIndexController extends Screen
{
    public function __construct(private readonly FactionService $service)
    {
    }

    public function name(): string
    {
        return __('Factions');
    }

    /**
     * Command bar.
     *
     * @return array<int, Action>
     */
    public function commandBar(): array
    {
        return [
            Link::make(__('New faction'))
                ->route(FactionRouteName::CREATE->value)
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

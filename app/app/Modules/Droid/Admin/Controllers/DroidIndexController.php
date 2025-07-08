<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Controllers;

use App\Modules\Droid\Admin\Components\Layouts\Index\FilterLayout;
use App\Modules\Droid\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Droid\Admin\Services\DroidService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;

class DroidIndexController extends Screen
{
    public function __construct(private readonly DroidService $service)
    {
    }

    public function name(): string
    {
        return __('Droids');
    }

    /**
     * Command bar.
     *
     * @return array<int, Action>
     */
    public function commandBar(): array
    {
        return [
            Link::make(__('New droid'))
                ->route(name: DroidRouteName::CREATE->value, absolute: false)
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

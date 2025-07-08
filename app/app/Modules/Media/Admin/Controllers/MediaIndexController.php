<?php

declare(strict_types=1);

namespace App\Modules\Media\Admin\Controllers;

use App\Modules\Media\Admin\Components\Layouts\Index\FilterLayout;
use App\Modules\Media\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Media\Admin\Enums\MediaRouteName;
use App\Modules\Media\Admin\Services\MediaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;

class MediaIndexController extends Screen
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
            Link::make(__('New media'))
                ->route(MediaRouteName::CREATE->value)
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

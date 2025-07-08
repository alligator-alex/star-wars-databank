<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Admin\Controllers;

use App\Modules\Handbook\Admin\Components\Layouts\Index\FilterLayout;
use App\Modules\Handbook\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Handbook\Admin\Services\HandbookValueService;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;

class HandbookValueIndexController extends Screen
{
    public function __construct(
        private readonly HandbookRepository $handbookRepository,
        private readonly HandbookValueService $handbookValueService,
    ) {
    }

    public function name(): string
    {
        return $this->handbook()->name ?? __('Values');
    }

    private function handbook(): ?Handbook
    {
        /** @var Route $currentRoute */
        $currentRoute = Request::route();

        static $handbook = null;
        if ($handbook === null) {
            $handbook = $this->handbookRepository->findOneById((int) $currentRoute->parameter('handbookId'));
        }

        return $handbook;
    }

    /**
     * Command bar.
     *
     * @return array<int, Action>
     */
    public function commandBar(): array
    {
        return [
            Link::make(__('New') . ' ' . Str::singular(mb_strtolower($this->handbook()?->name)))
                ->route(HandbookValueRouteName::CREATE->value, [
                    'handbookId' => $this->handbook()?->id,
                ], false)
                ->icon('plus-circle')
                ->class('btn icon-link rounded')
                ->type(Color::PRIMARY),
        ];
    }

    /**
     * @return array<string, LengthAwarePaginator>
     */
    public function query(int $handbookId, FilterLayout $filter): array
    {
        return [
            'items' => $this->handbookValueService->findPaginated($handbookId, $filter),
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

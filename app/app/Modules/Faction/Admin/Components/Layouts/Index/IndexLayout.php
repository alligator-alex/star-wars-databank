<?php

declare(strict_types=1);

namespace App\Modules\Faction\Admin\Components\Layouts\Index;

use App\Modules\Core\Admin\Components\Cells\DateTimeSplit;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Faction\Common\Models\Faction;
use Illuminate\Contracts\View\View;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Cell;
use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class IndexLayout extends Table
{
    public const int NAME_SYMBOL_LIMIT = 35;

    protected $target = 'items';

    /**
     * @return Cell[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', __('ID'))
                ->cantHide()
                ->sort()
                ->render(static fn (Faction $model): Field => Link::make('# ' . $model->id)
                    ->route(FactionRouteName::EDIT->value, $model->id, false)
                    ->class('text-muted'))
                ->width('100px'),

            TD::make('name', __('Name'))
                ->cantHide()
                ->sort()
                ->render(static fn (Faction $model): View => view('admin.factions.index.name-cell', [
                    'model' => $model,
                ]))
                ->width('300px'),

            TD::make('sort', __('Sort'))
                ->sort(),

            TD::make('created_at', __('Created'))
                ->defaultHidden()
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->sort()
                ->width('140px'),

            TD::make('updated_at', __('Last edit'))
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->sort()
                ->width('140px'),

            TD::make()
                ->cantHide()
                ->align(TD::ALIGN_CENTER)
                ->width('20px')
                ->render(static fn (Faction $model): DropDown => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route(FactionRouteName::EDIT->value, $model->id, false)
                            ->icon('bs.pencil'),

                        Button::make($model->isPublished() ? __('Unpublish') : __('Publish'))
                            ->icon($model->isPublished() ? 'bs.eye-slash' : 'bs.eye')
                            ->route(FactionRouteName::TOGGLE_PUBLISH->value, $model->id, false),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->type(Color::DANGER)
                            ->route(FactionRouteName::DELETE->value, $model->id, false)
                            ->confirm(__('This action cannot be undone!')),
                    ])),
        ];
    }
}

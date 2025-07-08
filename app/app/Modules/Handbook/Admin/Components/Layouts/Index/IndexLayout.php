<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Admin\Components\Layouts\Index;

use App\Modules\Core\Admin\Components\Cells\DateTimeSplit;
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Handbook\Common\Models\HandbookValue;
use Illuminate\Contracts\View\View;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Cell;
use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use ReflectionException;

class IndexLayout extends Table
{
    protected $target = 'items';

    /**
     * @return Cell[]
     *
     * @throws ReflectionException
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', __('ID'))
                ->cantHide()
                ->sort()
                ->render(static fn (HandbookValue $model): Field => Link::make('# ' . $model->id)
                    ->route(HandbookValueRouteName::EDIT->value, [
                        'handbookId' => $model->handbook->id,
                        'handbookValueId' => $model->id,
                    ], false)
                    ->class('text-muted'))
                ->width('100px'),

            TD::make('name', __('Name'))
                ->cantHide()
                ->sort()
                ->render(static fn (HandbookValue $model): View => view('admin.handbooks.index.name-cell', [
                    'model' => $model,
                ])),

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
                ->render(static fn (HandbookValue $model): DropDown => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route(HandbookValueRouteName::EDIT->value, [
                                'handbookId' => $model->handbook->id,
                                'handbookValueId' => $model->id,
                            ], false)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->type(Color::DANGER)
                            ->route(HandbookValueRouteName::DELETE->value, [
                                'handbookId' => $model->handbook->id,
                                'handbookValueId' => $model->id,
                            ], false)
                            ->confirm(__('This action cannot be undone!')),
                    ])),
        ];
    }
}

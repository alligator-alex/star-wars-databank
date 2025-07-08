<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Components\Layouts\Index;

use App\Modules\Core\Admin\Components\Cells\DateTimeSplit;
use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Faction\Admin\Traits\LayoutWithFactionsLinks;
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Manufacturer\Admin\Traits\LayoutWithManufacturersLinks;
use App\Modules\Media\Admin\Traits\LayoutWithMediaLinks;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Cell;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Throwable;

class IndexLayout extends Table
{
    use LayoutWithFactionsLinks;
    use LayoutWithManufacturersLinks;
    use LayoutWithMediaLinks;

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
                ->render(static fn (Droid $model): View => view('admin.droids.index.image-cell', [
                    'model' => $model,
                ]))
                ->width('100px'),

            TD::make('name', __('Name'))
                ->cantHide()
                ->sort()
                ->render(static fn (Droid $model): View => view('admin.droids.index.name-cell', [
                    'model' => $model,
                ]))
                ->width('300px'),

            TD::make('sort', __('Sort'))
                ->sort(),

            TD::make('line', __('Class'))
                ->render(fn (Droid $model): string => $this->classLink($model)),

            TD::make('manufacturers', __('Manufacturers'))
                ->defaultHidden()
                ->render(fn (Droid $model): string => $this->manufacturersLinks($model)),

            TD::make('factions', __('Factions'))
                ->defaultHidden()
                ->render(fn (Droid $model): string => $this->factionsLinks($model)),

            TD::make('appearances', __('Appearances'))
                ->defaultHidden()
                ->render(fn (Droid $model): string => $this->mediaLinks($model)),

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
                ->render(static fn (Droid $model): DropDown => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route(DroidRouteName::EDIT->value, $model->id, false)
                            ->icon('bs.pencil'),

                        Button::make($model->isPublished() ? __('Unpublish') : __('Publish'))
                            ->icon($model->isPublished() ? 'bs.eye-slash' : 'bs.eye')
                            ->route(
                                DroidRouteName::TOGGLE_PUBLISH->value,
                                array_merge([$model->id], Request::query()),
                                false
                            ),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->type(Color::DANGER)
                            ->route(DroidRouteName::DELETE->value, $model->id, false)
                            ->confirm(__('This action cannot be undone!')),
                    ])),
        ];
    }

    /**
     * @param Droid $model
     *
     * @return string
     *
     * @throws Throwable
     */
    private function classLink(Droid $model): string
    {
        if (!$model->class) {
            return '-';
        }

        return Link::make($model->class->name)
            ->type(Color::DEFAULT)
            ->route(HandbookValueRouteName::EDIT->value, [
                'handbookId' => $model->class->handbook_id,
                'handbookValueId' => $model->class->id,
            ], false)
            ->target('_blank')
            ->toHtml();
    }
}

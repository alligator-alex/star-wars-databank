<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Components\Layouts\Index;

use App\Modules\Core\Admin\Components\Cells\DateTimeSplit;
use App\Modules\Faction\Admin\Traits\LayoutWithFactionsLinks;
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Manufacturer\Admin\Traits\LayoutWithManufacturersLinks;
use App\Modules\Media\Admin\Traits\LayoutWithMediaLinks;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
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
                ->render(static fn (Vehicle $model): View => view('admin.vehicles.index.image-cell', [
                    'model' => $model,
                ]))
                ->width('100px'),

            TD::make('name', __('Name'))
                ->cantHide()
                ->sort()
                ->render(static fn (Vehicle $model): View => view('admin.vehicles.index.name-cell', [
                    'model' => $model,
                ]))
                ->width('300px'),

            TD::make('sort', __('Sort'))
                ->sort(),

            TD::make('line', __('Line'))
                ->render(fn (Vehicle $model): string => $this->lineLink($model)),

            TD::make('manufacturers', __('Manufacturers'))
                ->defaultHidden()
                ->render(fn (Vehicle $model): string => $this->manufacturersLinks($model)),

            TD::make('factions', __('Factions'))
                ->defaultHidden()
                ->render(fn (Vehicle $model): string => $this->factionsLinks($model)),

            TD::make('appearances', __('Appearances'))
                ->defaultHidden()
                ->render(fn (Vehicle $model): string => $this->mediaLinks($model)),

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
                ->render(static fn (Vehicle $model): DropDown => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route(VehicleRouteName::EDIT->value, $model->id, false)
                            ->icon('bs.pencil'),

                        Button::make($model->isPublished() ? __('Unpublish') : __('Publish'))
                            ->icon($model->isPublished() ? 'bs.eye-slash' : 'bs.eye')
                            ->route(
                                VehicleRouteName::TOGGLE_PUBLISH->value,
                                array_merge([$model->id], Request::query()),
                                false
                            ),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->type(Color::DANGER)
                            ->route(VehicleRouteName::DELETE->value, $model->id, false)
                            ->confirm(__('This action cannot be undone!')),
                    ])),
        ];
    }

    /**
     * @param Vehicle $model
     *
     * @return string
     *
     * @throws Throwable
     */
    private function lineLink(Vehicle $model): string
    {
        if (!$model->line) {
            return '-';
        }

        return Link::make(Str::limit($model->line->name))
            ->type(Color::DEFAULT)
            ->route(HandbookValueRouteName::EDIT->value, [
                'handbookId' => $model->line->handbook_id,
                'handbookValueId' => $model->line->id,
            ], false)
            ->target('_blank')
            ->toHtml();
    }
}

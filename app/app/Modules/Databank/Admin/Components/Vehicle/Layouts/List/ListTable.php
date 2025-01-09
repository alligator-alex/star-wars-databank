<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Layouts\List;

use App\Modules\Core\Admin\Components\Cells\DateTimeSplit;
use App\Modules\Databank\Admin\Components\Faction\Layouts\List\ListTable as FactionListTable;
use App\Modules\Databank\Admin\Components\Line\Layouts\List\ListTable as LineListTable;
use App\Modules\Databank\Admin\Components\Manufacturer\Layouts\List\ListTable as ManufacturerListTable;
use App\Modules\Databank\Admin\Components\Media\Layouts\List\ListTable as MediaListTable;
use App\Modules\Databank\Admin\Enums\FactionRouteName;
use App\Modules\Databank\Admin\Enums\LineRouteName;
use App\Modules\Databank\Admin\Enums\ManufacturerRouteName;
use App\Modules\Databank\Admin\Enums\VehicleRouteName;
use App\Modules\Databank\Admin\Enums\MediaRouteName;
use App\Modules\Databank\Common\Models\Faction;
use App\Modules\Databank\Common\Models\Line;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Models\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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

class ListTable extends Table
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
                ->render(static fn (Vehicle $model): View => view('admin.databank.vehicles.list.image-cell', [
                    'model' => $model,
                ]))
                ->width('100px'),

            TD::make('name', __('Name'))
                ->cantHide()
                ->sort()
                ->render(static fn (Vehicle $model): View => view('admin.databank.vehicles.list.name-cell', [
                    'model' => $model,
                ]))
                ->width('300px'),

            TD::make('sort', __('Sort'))
                ->sort(),

            TD::make('line', __('Line'))
                ->render(fn (Vehicle $model): string => $this->vehicleLineLink($model)),

            TD::make('manufacturers', __('Manufacturers'))
                ->defaultHidden()
                ->render(fn (Vehicle $model): string => $this->vehicleManufacturersLinks($model)),

            TD::make('factions', __('Factions'))
                ->defaultHidden()
                ->render(fn (Vehicle $model): string => $this->vehicleFactionsLinks($model)),

            TD::make('appearances', __('Appearances'))
                ->defaultHidden()
                ->render(fn (Vehicle $model): string => $this->vehicleAppearancesLinks($model)),

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
                            ->route(VehicleRouteName::ONE->value, $model->id, false)
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
    private function vehicleLineLink(Vehicle $model): string
    {
        /** @var Builder|Line $query */
        $query = $model->line();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        $line = $query->first();
        if (!$line) {
            return '-';
        }

        return Link::make(Str::limit($line->name, LineListTable::NAME_SYMBOL_LIMIT))
            ->type(Color::DEFAULT)
            ->route(LineRouteName::ONE->value, $line->id, false)
            ->target('_blank')
            ->toHtml();
    }

    /**
     * @param Vehicle $model
     *
     * @return string
     *
     * @throws Throwable
     */
    private function vehicleManufacturersLinks(Vehicle $model): string
    {
        /** @var Builder|Manufacturer $query */
        $query = $model->manufacturers();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        if ($query->count() === 0) {
            return '-';
        }

        $html = '';

        /** @var Manufacturer $manufacturer */
        foreach ($query->cursor() as $manufacturer) {
            $html .= Link::make(Str::limit($manufacturer->name, ManufacturerListTable::NAME_SYMBOL_LIMIT))
                    ->type(Color::DEFAULT)
                    ->route(ManufacturerRouteName::ONE->value, $manufacturer->id, false)
                    ->target('_blank')
                    ->toHtml() . PHP_EOL;
        }

        return $html;
    }

    /**
     * @param Vehicle $model
     *
     * @return string
     *
     * @throws Throwable
     */
    private function vehicleFactionsLinks(Vehicle $model): string
    {
        /** @var Builder|Faction $query */
        $query = $model->factions();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        if ($query->count() === 0) {
            return '-';
        }

        $html = '';

        /** @var Faction $faction */
        foreach ($query->cursor() as $faction) {
            $html .= Link::make(Str::limit($faction->name, FactionListTable::NAME_SYMBOL_LIMIT))
                    ->type(Color::DEFAULT)
                    ->route(FactionRouteName::ONE->value, $faction->id, false)
                    ->target('_blank')
                    ->toHtml() . PHP_EOL;
        }

        return $html;
    }

    /**
     * @param Vehicle $model
     *
     * @return string
     *
     * @throws Throwable
     */
    private function vehicleAppearancesLinks(Vehicle $model): string
    {
        /** @var Builder|Media $query */
        $query = $model->appearances();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        if ($query->count() === 0) {
            return '-';
        }

        $html = '';

        /** @var Media $media */
        foreach ($query->cursor() as $media) {
            $html .= Link::make(Str::limit($media->name, MediaListTable::NAME_SYMBOL_LIMIT))
                    ->type(Color::DEFAULT)
                    ->route(MediaRouteName::ONE->value, $media->id, false)
                    ->target('_blank')
                    ->toHtml() . PHP_EOL;
        }

        return $html;
    }
}

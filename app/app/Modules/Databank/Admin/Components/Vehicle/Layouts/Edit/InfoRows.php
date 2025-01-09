<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Layouts\Edit;

use App\Modules\Core\Admin\Components\Fields\Select;
use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Common\Enums\VehicleType;
use App\Modules\Databank\Common\Models\Faction;
use App\Modules\Databank\Common\Models\Line;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Models\Vehicle;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Vehicle getModel()
 */
class InfoRows extends Rows
{
    use LayoutWithModel;

    protected $title = 'Info';

    protected function fields(): iterable
    {
        return [
            Group::make([
                Select::make('category')
                    ->title(__('Category'))
                    ->fromEnum(VehicleCategory::class, 'nameForHumans')
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->getModel()->category?->value),

                Select::make('type')
                    ->title(__('Type'))
                    ->fromEnum(VehicleType::class, 'nameForHumans')
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->getModel()->type?->value),

                Select::make('lineId')
                    ->title(__('Line'))
                    ->options(Line::dropdownList(true))
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->selectedLineId()),
            ]),

            Group::make([
                Select::make('manufacturersIds.')
                    ->title(__('Manufactured by'))
                    ->options(Manufacturer::dropdownList(true))
                    ->value($this->selectedManufacturersIds())
                    ->multiple(),

                Select::make('factionsIds.')
                    ->title(__('Used by'))
                    ->options(Faction::dropdownList(true))
                    ->value($this->selectedFactionsIds())
                    ->multiple(),

                Select::make('mainFactionId')
                    ->title(__('Mainly used by'))
                    ->options(Faction::dropdownList(true))
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->selectedMainFactionId()),
            ]),

            Group::make([
                Picture::make('imageId')
                    ->title(__('Image'))
                    ->groups(AttachmentGroup::VEHICLE_IMAGE->value)
                    ->targetId()
                    ->value($this->getModel()->image_id),
            ]),

            Group::make([
                Quill::make('description')
                    ->title(__('Description'))
                    ->toolbar(['text', 'header', 'list', 'format'])
                    ->value($this->getModel()->description),
            ]),
        ];
    }

    /**
     * @return int|null
     */
    private function selectedLineId(): ?int
    {
        /** @var BelongsToMany|Line $query */
        $query = $this->getModel()->line();

        /** @var Line|null $model */
        $model = $query->withDrafts()->first();

        return $model?->id;
    }

    /**
     * @return array<int, string>
     */
    private function selectedManufacturersIds(): array
    {
        /** @var BelongsToMany|Manufacturer $query */
        $query = $this->getModel()->manufacturers();

        return $query->withDrafts()
            ->pluck(Manufacturer::tableName() . '.id')
            ->toArray();
    }

    /**
     * @return array<int, string>
     */
    private function selectedFactionsIds(): array
    {
        /** @var BelongsToMany|Faction $query */
        $query = $this->getModel()->factions();

        return $query->withDrafts()
            ->pluck(Faction::tableName() . '.id')
            ->toArray();
    }

    /**
     * @return int|null
     */
    private function selectedMainFactionId(): ?int
    {
        /** @var HasOneThrough|Faction $query */
        $query = $this->getModel()->mainFaction();

        /** @var Faction|null $model */
        $model = $query->withDrafts()->first();

        return $model?->id;
    }
}

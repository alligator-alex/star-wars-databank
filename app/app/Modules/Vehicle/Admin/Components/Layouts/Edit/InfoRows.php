<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Components\Layouts\Edit;

use App\Modules\Core\Admin\Components\Fields\Select;
use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Vehicle model()
 */
class InfoRows extends Rows
{
    use LayoutWithModel;

    protected $title = 'Info';

    /**
     * @param array<string, string> $categoryDropdownList
     * @param array<string, string> $typeDropdownList
     * @param array<string, string> $lineDropdownList
     * @param array<string, string> $manufacturerDropdownList
     * @param array<string, string> $factionDropdownList
     */
    public function __construct(
        private readonly array $categoryDropdownList,
        private readonly array $typeDropdownList,
        private readonly array $lineDropdownList,
        private readonly array $manufacturerDropdownList,
        private readonly array $factionDropdownList
    ) {
    }

    protected function fields(): iterable
    {
        return [
            Group::make([
                Select::make('categoryId')
                    ->title(__('Category'))
                    ->options($this->categoryDropdownList)
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->model()->category_id),

                Select::make('typeId')
                    ->title(__('Type'))
                    ->options($this->typeDropdownList)
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->model()->type_id),

                Select::make('lineId')
                    ->title(__('Line'))
                    ->options($this->lineDropdownList)
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->model()->line_id),
            ]),

            Group::make([
                Select::make('manufacturersIds.')
                    ->title(__('Manufactured by'))
                    ->options($this->manufacturerDropdownList)
                    ->value($this->selectedManufacturersIds())
                    ->multiple(),

                Select::make('factionsIds.')
                    ->title(__('Used by'))
                    ->options($this->factionDropdownList)
                    ->value($this->selectedFactionsIds())
                    ->multiple(),

                Select::make('mainFactionId')
                    ->title(__('Mainly used by'))
                    ->options($this->factionDropdownList)
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->selectedMainFactionId()),
            ]),

            Group::make([
                Picture::make('imageId')
                    ->title(__('Image'))
                    ->groups(AttachmentGroup::VEHICLE_IMAGE->value)
                    ->targetId()
                    ->value($this->model()->image_id),
            ]),

            Group::make([
                Quill::make('description')
                    ->title(__('Description'))
                    ->toolbar(['text', 'header', 'list', 'format'])
                    ->value($this->model()->description),
            ]),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function selectedManufacturersIds(): array
    {
        /** @var BelongsToMany|Manufacturer $query */
        $query = $this->model()->manufacturers();

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
        $query = $this->model()->factions();

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
        $query = $this->model()->mainFaction();

        /** @var Faction|null $model */
        $model = $query->withDrafts()->first();

        return $model?->id;
    }
}

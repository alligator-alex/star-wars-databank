<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Components\Layouts\Edit;

use App\Modules\Core\Admin\Components\Fields\Select;
use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Droid model()
 */
class InfoRows extends Rows
{
    use LayoutWithModel;

    protected $title = 'Info';

    protected function fields(): iterable
    {
        return [
            Group::make([
                Select::make('lineId')
                    ->title(__('Line'))
                    ->options(HandbookValue::dropdownList(HandbookType::DROID_LINE))
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->model()->line_id),

                Select::make('modelId')
                    ->title(__('Model'))
                    ->options(HandbookValue::dropdownList(HandbookType::DROID_MODEL))
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->model()->model_id),

                Select::make('classId')
                    ->title(__('Class'))
                    ->options(HandbookValue::dropdownList(HandbookType::DROID_CLASS))
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->model()->class_id),
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
                    ->groups(AttachmentGroup::DROID_IMAGE->value)
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

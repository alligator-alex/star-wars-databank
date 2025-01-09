<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Layouts\Edit;

use App\Modules\Core\Admin\Components\Fields\Select;
use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Vehicle getModel()
 */
class AppearancesRows extends Rows
{
    use LayoutWithModel;

    protected $title = 'Appearances';

    protected function fields(): iterable
    {
        return [
            Group::make([
                Select::make('appearancesIds.')
                    ->title(__('Appeared in'))
                    ->multiple()
                    ->options(Media::dropdownList(true))
                    ->value($this->selectedAppearancesIds()),
            ]),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function selectedAppearancesIds(): array
    {
        /** @var Vehicle $model */
        $model = $this->getModel();

        /** @var BelongsToMany|Media $query */
        $query = $model->appearances();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        return $query->pluck(Media::tableName() . '.id')->toArray();
    }
}

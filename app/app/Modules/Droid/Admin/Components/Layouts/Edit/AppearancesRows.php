<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Components\Layouts\Edit;

use App\Modules\Core\Admin\Components\Fields\Select;
use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Media\Common\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Droid model()
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
        /** @var BelongsToMany|Media $query */
        $query = $this->model()->appearances();

        $query->withDrafts();

        return $query->pluck(Media::tableName() . '.id')->toArray();
    }
}

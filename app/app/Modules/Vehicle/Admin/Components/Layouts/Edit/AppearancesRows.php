<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Components\Layouts\Edit;

use App\Modules\Core\Admin\Components\Fields\Select;
use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Vehicle model()
 */
class AppearancesRows extends Rows
{
    use LayoutWithModel;

    protected $title = 'Appearances';

    /**
     * @param array<string, array<int, string>> $mediaDropdownList
     */
    public function __construct(private readonly array $mediaDropdownList)
    {
    }

    protected function fields(): iterable
    {
        return [
            Group::make([
                Select::make('appearancesIds.')
                    ->title(__('Appeared in'))
                    ->multiple()
                    ->options($this->mediaDropdownList)
                    ->value($this->selectedMediaIds()),
            ]),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function selectedMediaIds(): array
    {
        $model = $this->model();

        /** @var BelongsToMany|Media $query */
        $query = $model->appearances();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        return $query->pluck(Media::tableName() . '.id')->toArray();
    }
}

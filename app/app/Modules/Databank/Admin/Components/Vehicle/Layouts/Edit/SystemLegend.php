<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Layouts\Edit;

use App\Modules\Core\Admin\Components\Cells\DateTimeSplit;
use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Databank\Common\Models\Vehicle;
use Orchid\Screen\Cell;
use Orchid\Screen\Layouts\Legend;
use Orchid\Screen\Sight;

/**
 * @method Vehicle getModel()
 */
class SystemLegend extends Legend
{
    use LayoutWithModel;

    protected $title = 'System';

    /**
     * @return Cell[]
     */
    protected function columns(): iterable
    {
        return [
            Sight::make('id', __('ID'))
                ->render(fn () => $this->getModel()->id),
            Sight::make('createdAt', __('Created'))
                ->usingComponent(DateTimeSplit::class, value: $this->getModel()->created_at),
            Sight::make('updatedAt', __('Last edit'))
                ->usingComponent(DateTimeSplit::class, value: $this->getModel()->updated_at),
        ];
    }
}

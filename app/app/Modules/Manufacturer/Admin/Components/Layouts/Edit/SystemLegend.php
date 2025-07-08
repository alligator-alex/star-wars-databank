<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Admin\Components\Layouts\Edit;

use App\Modules\Core\Admin\Components\Cells\DateTimeSplit;
use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use Orchid\Screen\Cell;
use Orchid\Screen\Layouts\Legend;
use Orchid\Screen\Sight;

/**
 * @method Manufacturer model()
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
                ->render(fn () => $this->model()->id),
            Sight::make('createdAt', __('Created'))
                ->usingComponent(DateTimeSplit::class, value: $this->model()->created_at),
            Sight::make('updatedAt', __('Last edit'))
                ->usingComponent(DateTimeSplit::class, value: $this->model()->updated_at),
        ];
    }
}

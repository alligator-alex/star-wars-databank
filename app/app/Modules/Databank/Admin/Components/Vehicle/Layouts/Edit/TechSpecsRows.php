<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Layouts\Edit;

use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Databank\Common\Models\Vehicle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Vehicle getModel()
 */
class TechSpecsRows extends Rows
{
    use LayoutWithModel;

    protected $title = 'Technical specifications';

    protected function fields(): iterable
    {
        $techSpecs = $this->getModel()->getTechnicalSpecifications();
        if (is_null($techSpecs)) {
            return [
                Input::make('techSpecs.noCategory')
                    ->value('Please set category first and save the model')
                    ->disabled()
                    ->horizontal(),
            ];
        }

        $this->title .= ' (' . $this->getModel()->category->nameForHumans() . ')';

        $fields = [];
        foreach ($techSpecs->toArray() as $name => $value) {
            $fields[] = Input::make('techSpecs.' . $name)
                ->title(__($name))
                ->value($value)
                ->horizontal();
        }

        return $fields;
    }
}

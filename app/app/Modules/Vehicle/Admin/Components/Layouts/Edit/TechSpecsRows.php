<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Components\Layouts\Edit;

use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Vehicle model()
 */
class TechSpecsRows extends Rows
{
    use LayoutWithModel;

    protected $title = 'Technical specifications';

    protected function fields(): iterable
    {
        $techSpecs = $this->model()->getTechnicalSpecifications();
        if ($techSpecs === null) {
            return [
                Input::make('techSpecs.noCategory')
                    ->value('Please set category first and save the model')
                    ->disabled()
                    ->horizontal(),
            ];
        }

        $this->title .= ' (' . $this->model()->category->name . ')';

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

<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Components\Layouts\Edit;

use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Droid\Common\DTOs\TechSpecs;
use App\Modules\Droid\Common\Models\Droid;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Droid model()
 */
class TechSpecsRows extends Rows
{
    use LayoutWithModel;

    protected $title = 'Technical specifications';

    protected function fields(): iterable
    {
        $techSpecs = $this->model()->getTechnicalSpecifications() ?? new TechSpecs();

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

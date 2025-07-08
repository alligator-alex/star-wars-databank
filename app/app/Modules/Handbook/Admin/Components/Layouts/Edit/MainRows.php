<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Admin\Components\Layouts\Edit;

use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Handbook\Common\Models\HandbookValue;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

/**
 * @method HandbookValue model()
 */
class MainRows extends Rows
{
    use LayoutWithModel;

    protected $title = 'Main';

    protected function fields(): iterable
    {
        return [
            Group::make([
                Input::make('name')
                    ->title(__('Name'))
                    ->value($this->model()->name),

                Input::make('slug')
                    ->title(__('Slug'))
                    ->value($this->model()->slug)
                    ->help(__('Used as a part of public URL')),
            ]),
        ];
    }
}

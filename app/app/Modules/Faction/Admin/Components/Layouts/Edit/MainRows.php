<?php

declare(strict_types=1);

namespace App\Modules\Faction\Admin\Components\Layouts\Edit;

use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Faction\Common\Models\Faction;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Faction model()
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

            Group::make([
                RadioButtons::make('status')
                    ->title(__('Status'))
                    ->options([
                        Status::DRAFT->value => __('Draft'),
                        Status::PUBLISHED->value => __('Published'),
                    ])
                    ->value($this->model()->status->value)
                    ->help(__('Drafts are not visible to public')),

                Input::make('sort')
                    ->title(__('Sort'))
                    ->type('number')
                    ->value($this->model()->sort)
                    ->help(__('Lower number means higher position')),
            ]),
        ];
    }
}

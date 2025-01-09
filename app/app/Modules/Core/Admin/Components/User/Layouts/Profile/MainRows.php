<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Components\User\Layouts\Profile;

use App\Modules\Core\Common\Models\User;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class MainRows extends Rows
{
    public function fields(): array
    {
        /** @var User $model */
        $model = $this->query->get('model');

        return [
            Input::make('name')
                ->type('text')
                ->title(__('Name'))
                ->placeholder(__('Name'))
                ->value($model->name),

            Input::make('email')
                ->type('email')
                ->title(__('Email'))
                ->placeholder(__('Email'))
                ->value($model->email),
        ];
    }
}

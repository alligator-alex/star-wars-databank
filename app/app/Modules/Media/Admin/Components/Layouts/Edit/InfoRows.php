<?php

declare(strict_types=1);

namespace App\Modules\Media\Admin\Components\Layouts\Edit;

use App\Modules\Core\Admin\Components\Fields\Select;
use App\Modules\Core\Admin\Traits\LayoutWithModel;
use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Media\Common\Enums\MediaType;
use App\Modules\Media\Common\Models\Media;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Layouts\Rows;

/**
 * @method Media model()
 */
class InfoRows extends Rows
{
    use LayoutWithModel;

    protected $title = 'Info';

    protected function fields(): iterable
    {
        return [
            Group::make([
                Select::make('type')
                    ->title(__('Type'))
                    ->fromEnum(MediaType::class, 'nameForHumans')
                    ->empty(__('None'))
                    ->set('placeholder', __('Select...'))
                    ->value($this->model()->type?->value),

                DateTimer::make('releaseDate')
                    ->title(__('Release date'))
                    ->allowInput()
                    ->format('Y-m-d')
                    ->serverFormat('Y-m-d')
                    ->value($this->model()->release_date?->format('Y-m-d')),
            ]),

            Group::make([
                Picture::make('posterId')
                    ->title(__('Poster'))
                    ->groups(AttachmentGroup::MEDIA_POSTER->value)
                    ->targetId()
                    ->value($this->model()->poster_id),
            ]),
        ];
    }
}

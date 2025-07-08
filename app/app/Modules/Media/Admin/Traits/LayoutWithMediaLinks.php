<?php

declare(strict_types=1);

namespace App\Modules\Media\Admin\Traits;

use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Media\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Media\Admin\Enums\MediaRouteName;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Color;
use Throwable;

trait LayoutWithMediaLinks
{
    /**
     * @param Vehicle|Droid $model
     *
     * @return string
     *
     * @throws Throwable
     */
    private function mediaLinks(Vehicle|Droid $model): string
    {
        /** @var Builder|Media $query */
        $query = $model->appearances();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        if ($query->count() === 0) {
            return '-';
        }

        $html = '';

        /** @var Media $media */
        foreach ($query->cursor() as $media) {
            $html .= Link::make(Str::limit($media->name, IndexLayout::NAME_SYMBOL_LIMIT))
                ->type(Color::DEFAULT)
                ->route(MediaRouteName::EDIT->value, $media->id, false)
                ->target('_blank')
                ->toHtml() . PHP_EOL;
        }

        return $html;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Faction\Admin\Traits;

use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Faction\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Color;
use Throwable;

trait LayoutWithFactionsLinks
{
    /**
     * @param Vehicle|Droid $model
     *
     * @return string
     *
     * @throws Throwable
     */
    private function factionsLinks(Vehicle|Droid $model): string
    {
        /** @var Builder|Faction $query */
        $query = $model->factions();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        if ($query->count() === 0) {
            return '-';
        }

        $html = '';

        /** @var Faction $faction */
        foreach ($query->cursor() as $faction) {
            $html .= Link::make(Str::limit($faction->name, IndexLayout::NAME_SYMBOL_LIMIT))
                ->type(Color::DEFAULT)
                ->route(FactionRouteName::EDIT->value, $faction->id, false)
                ->target('_blank')
                ->toHtml() . PHP_EOL;
        }

        return $html;
    }
}

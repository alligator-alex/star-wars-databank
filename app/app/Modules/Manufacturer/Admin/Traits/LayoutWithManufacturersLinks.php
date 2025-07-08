<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Admin\Traits;

use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Manufacturer\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Manufacturer\Admin\Enums\ManufacturerRouteName;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Color;
use Throwable;

trait LayoutWithManufacturersLinks
{
    /**
     * @param Vehicle|Droid $model
     *
     * @return string
     *
     * @throws Throwable
     */
    private function manufacturersLinks(Vehicle|Droid $model): string
    {
        /** @var Builder|Manufacturer $query */
        $query = $model->manufacturers();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        if ($query->count() === 0) {
            return '-';
        }

        $html = '';

        /** @var Manufacturer $manufacturer */
        foreach ($query->cursor() as $manufacturer) {
            $html .= Link::make(Str::limit($manufacturer->name, IndexLayout::NAME_SYMBOL_LIMIT))
                ->type(Color::DEFAULT)
                ->route(ManufacturerRouteName::EDIT->value, $manufacturer->id, false)
                ->target('_blank')
                ->toHtml() . PHP_EOL;
        }

        return $html;
    }
}

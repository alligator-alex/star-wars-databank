<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Layouts\List;

use App\Modules\Databank\Admin\Components\Filters\NameFilter;
use App\Modules\Databank\Admin\Components\Filters\StatusFilter;
use App\Modules\Databank\Admin\Components\Vehicle\Filters\CategoryFilter;
use App\Modules\Databank\Admin\Components\Vehicle\Filters\FactionFilter;
use App\Modules\Databank\Admin\Components\Vehicle\Filters\LineFilter;
use App\Modules\Databank\Admin\Components\Vehicle\Filters\ManufacturerFilter;
use App\Modules\Databank\Admin\Components\Vehicle\Filters\TypeFilter;
use App\Modules\Databank\Admin\Components\Vehicle\Filters\MediaFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class FilterSelection extends Selection
{
    public $template = 'admin.layouts.accordion-filter';

    /**
     * @return Filter[]|class-string[]
     */
    public function filters(): iterable
    {
        return [
            NameFilter::class,
            StatusFilter::class,
            CategoryFilter::class,
            TypeFilter::class,
            LineFilter::class,
            ManufacturerFilter::class,
            FactionFilter::class,
            MediaFilter::class,
        ];
    }
}

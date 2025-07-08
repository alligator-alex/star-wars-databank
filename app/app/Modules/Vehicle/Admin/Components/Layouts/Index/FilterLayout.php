<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Components\Layouts\Index;

use App\Modules\Databank\Admin\Components\Filters\FactionFilter;
use App\Modules\Databank\Admin\Components\Filters\ManufacturerFilter;
use App\Modules\Databank\Admin\Components\Filters\MediaFilter;
use App\Modules\Databank\Admin\Components\Filters\NameFilter;
use App\Modules\Databank\Admin\Components\Filters\StatusFilter;
use App\Modules\Vehicle\Admin\Components\Filters\CategoryFilter;
use App\Modules\Vehicle\Admin\Components\Filters\LineFilter;
use App\Modules\Vehicle\Admin\Components\Filters\TypeFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class FilterLayout extends Selection
{
    public $template = 'admin.common.layouts.accordion-filter';

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

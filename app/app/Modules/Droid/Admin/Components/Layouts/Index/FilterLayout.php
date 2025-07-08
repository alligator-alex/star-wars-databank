<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Components\Layouts\Index;

use App\Modules\Databank\Admin\Components\Filters\FactionFilter;
use App\Modules\Databank\Admin\Components\Filters\ManufacturerFilter;
use App\Modules\Databank\Admin\Components\Filters\MediaFilter;
use App\Modules\Databank\Admin\Components\Filters\NameFilter;
use App\Modules\Databank\Admin\Components\Filters\StatusFilter;
use App\Modules\Droid\Admin\Components\Filters\ClassFilter;
use App\Modules\Droid\Admin\Components\Filters\LineFilter;
use App\Modules\Droid\Admin\Components\Filters\ModelFilter;
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
            LineFilter::class,
            ModelFilter::class,
            ClassFilter::class,
            ManufacturerFilter::class,
            FactionFilter::class,
            MediaFilter::class,
        ];
    }
}

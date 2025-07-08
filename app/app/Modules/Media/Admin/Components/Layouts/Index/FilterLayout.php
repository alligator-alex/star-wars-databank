<?php

declare(strict_types=1);

namespace App\Modules\Media\Admin\Components\Layouts\Index;

use App\Modules\Databank\Admin\Components\Filters\NameFilter;
use App\Modules\Databank\Admin\Components\Filters\StatusFilter;
use App\Modules\Media\Admin\Components\Filters\TypeFilter;
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
            TypeFilter::class,
        ];
    }
}

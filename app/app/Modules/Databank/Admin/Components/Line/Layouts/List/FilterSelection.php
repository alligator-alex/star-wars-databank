<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Line\Layouts\List;

use App\Modules\Databank\Admin\Components\Filters\NameFilter;
use App\Modules\Databank\Admin\Components\Filters\StatusFilter;
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
        ];
    }
}

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
use App\Modules\Faction\Admin\Services\FactionService;
use App\Modules\Handbook\Admin\Services\HandbookValueService;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Manufacturer\Admin\Services\ManufacturerService;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class FilterLayout extends Selection
{
    public $template = 'admin.common.layouts.accordion-filter';

    public function __construct(
        private readonly ManufacturerService $manufacturerService,
        private readonly FactionService $factionService,
        private readonly HandbookValueService $handbookValueService
    ) {
    }

    /**
     * @return Filter[]|class-string[]
     */
    public function filters(): iterable
    {
        return [
            NameFilter::class,
            StatusFilter::class,
            new LineFilter($this->handbookValueService->dropdownList(HandbookType::DROID_LINE)),
            new ModelFilter($this->handbookValueService->dropdownList(HandbookType::DROID_MODEL)),
            new ClassFilter($this->handbookValueService->dropdownList(HandbookType::DROID_CLASS)),
            new ManufacturerFilter($this->manufacturerService->dropdownList()),
            new FactionFilter($this->factionService->dropdownList()),
            MediaFilter::class,
        ];
    }
}

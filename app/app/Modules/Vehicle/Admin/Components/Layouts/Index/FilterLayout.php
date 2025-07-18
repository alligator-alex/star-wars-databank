<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Components\Layouts\Index;

use App\Modules\Databank\Admin\Components\Filters\FactionFilter;
use App\Modules\Databank\Admin\Components\Filters\ManufacturerFilter;
use App\Modules\Databank\Admin\Components\Filters\MediaFilter;
use App\Modules\Databank\Admin\Components\Filters\NameFilter;
use App\Modules\Databank\Admin\Components\Filters\StatusFilter;
use App\Modules\Faction\Admin\Services\FactionService;
use App\Modules\Handbook\Admin\Services\HandbookValueService;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Manufacturer\Admin\Services\ManufacturerService;
use App\Modules\Vehicle\Admin\Components\Filters\CategoryFilter;
use App\Modules\Vehicle\Admin\Components\Filters\LineFilter;
use App\Modules\Vehicle\Admin\Components\Filters\TypeFilter;
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
            new CategoryFilter($this->handbookValueService->dropdownList(HandbookType::VEHICLE_CATEGORY)),
            new TypeFilter($this->handbookValueService->dropdownList(HandbookType::VEHICLE_TYPE)),
            new LineFilter($this->handbookValueService->dropdownList(HandbookType::VEHICLE_LINE)),
            new ManufacturerFilter($this->manufacturerService->dropdownList()),
            new FactionFilter($this->factionService->dropdownList()),
            MediaFilter::class,
        ];
    }
}

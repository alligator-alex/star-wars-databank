<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Public\Contollers;

use App\Modules\Faction\Public\Services\FactionService;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Public\Services\HandbookValueService;
use App\Modules\Manufacturer\Public\Services\ManufacturerService;
use App\Modules\Media\Public\Services\MediaService;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Public\Requests\FilterRequest;
use App\Modules\Vehicle\Public\Services\VehicleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class VehicleController
{
    public function __construct(
        private readonly VehicleService $service,
        private readonly MediaService $mediaService,
        private readonly FactionService $factionService,
        private readonly ManufacturerService $manufacturerService,
        private readonly HandbookValueService $handbookValueService
    ) {
    }

    public function index(FilterRequest $request): View|JsonResponse
    {
        $vehicles = $this->service->findPaginated($request, $request->getPage());

        /** @phpstan-ignore-next-line */
        $pagination = $vehicles->withQueryString()
            ->links('public.common.pagination-infinite', ['iconContent' => 'd4']);

        if ($request->ajax()) {
            return response()
                ->json([
                    'html' => [
                        'index' =>  view('public.vehicles.partials.index-content', [
                            'vehicles' => $vehicles,
                        ])->render(),
                        'pagination' => $pagination->toHtml(),
                    ],
                ]);
        }

        $appliedFilters = [
            'factions' => $request->getFactions(),
            'manufacturers' => $request->getManufacturers(),
            'media' => $request->getMedia(),
            'lines' => $request->getLines(),
            'categories' => $request->getCategories(),
            'types' => $request->getTypes(),
        ];

        return view('public.vehicles.index', [
            'appliedFilters' => $appliedFilters,
            'appliedFiltersCount' => count(array_filter($appliedFilters)),
            'vehicles' => $vehicles,
            'factions' => $this->factionService->dropdownList(),
            'manufacturers' => $this->manufacturerService->dropdownList(),
            'media' => $this->mediaService->dropdownList(),
            'categories' => $this->handbookValueService->dropdownList(HandbookType::VEHICLE_CATEGORY),
            'types' => $this->handbookValueService->dropdownList(HandbookType::VEHICLE_TYPE),
            'lines' => $this->handbookValueService->dropdownList(HandbookType::VEHICLE_LINE),
            'pagination' => $pagination,
        ]);
    }

    public function detail(string $slug): View
    {
        $vehicle = $this->service->findOneBySlug($slug, $this->isAdminPagePreview());
        if (!$vehicle) {
            abort(404);
        }

        return view('public.vehicles.detail', [
            'vehicle' => $vehicle,
        ]);
    }

    private function isAdminPagePreview(): bool
    {
        $referer = Request::header('referer');
        if (!$referer) {
            return false;
        }

        return str_starts_with($referer, route(VehicleRouteName::INDEX->value));
    }
}

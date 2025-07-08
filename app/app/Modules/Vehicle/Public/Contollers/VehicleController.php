<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Public\Contollers;

use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Public\Requests\FilterRequest;
use App\Modules\Vehicle\Public\Services\VehicleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class VehicleController
{
    public function __construct(private readonly VehicleService $service)
    {
    }

    public function index(FilterRequest $request): View|JsonResponse
    {
        $vehicles = $this->service->findPaginated($request);

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
            'factions' => Faction::dropdownList(columnAsKey: 'slug'),
            'manufacturers' => Manufacturer::dropdownList(columnAsKey: 'slug'),
            'media' => Media::dropdownList(columnAsKey: 'slug'),
            'categories' => HandbookValue::dropdownList(HandbookType::VEHICLE_CATEGORY, 'slug'),
            'types' => HandbookValue::dropdownList(HandbookType::VEHICLE_TYPE, 'slug'),
            'lines' => HandbookValue::dropdownList(HandbookType::VEHICLE_LINE, 'slug'),
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

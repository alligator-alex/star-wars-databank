<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Controllers;

use App\Modules\Databank\Admin\Enums\VehicleRouteName;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Common\Enums\VehicleType;
use App\Modules\Databank\Common\Models\Faction;
use App\Modules\Databank\Common\Models\Line;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Models\Media;
use App\Modules\Databank\Public\Requests\GetList;
use App\Modules\Databank\Public\Services\VehicleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class VehicleController
{
    public function __construct(private readonly VehicleService $service)
    {
    }

    public function list(GetList $request): View|JsonResponse
    {
        $vehicles = $this->service->findAllPaginated($request);

        /** @phpstan-ignore-next-line */
        $pagination = $vehicles->withQueryString()
            ->links('public.components.pagination-infinite');

        if ($request->ajax()) {
            return response()
                ->json([
                    'html' => [
                        'list' =>  view('public.vehicles.list.content', [
                            'vehicles' => $vehicles,
                        ])->render(),
                        'pagination' => $pagination->toHtml(),
                    ],
                ]);
        }

        $categories = VehicleCategory::cases();
        usort($categories, static fn (VehicleCategory $categoryA, VehicleCategory $categoryB) => strnatcmp(
            $categoryA->name,
            $categoryB->name
        ));

        $types = VehicleType::cases();
        usort($types, static fn (VehicleType $typeA, VehicleType $typeB) => strnatcmp($typeA->name, $typeB->name));

        // "Other" always at the end
        unset($types[array_search(VehicleType::OTHER, $types, true)]);
        $types[] = VehicleType::OTHER;

        return view('public.vehicles.list', [
            'appliedFilters' => [
                'factions' => $request->getFactions(),
                'manufacturers' => $request->getManufacturers(),
                'media' => $request->getMedia(),
                'lines' => $request->getLines(),
                'categories' => $request->getCategories(),
                'types' => $request->getTypes(),
            ],
            'vehicles' => $vehicles,
            'factions' => Faction::dropdownList(columnAsKey: 'slug'),
            'manufacturers' => Manufacturer::dropdownList(columnAsKey: 'slug'),
            'media' => Media::dropdownList(columnAsKey: 'slug'),
            'lines' => Line::dropdownList(columnAsKey: 'slug'),
            'categories' => $categories,
            'types' => $types,
            'pagination' => $pagination,
        ]);
    }

    public function one(string $slug): View
    {
        $vehicle = $this->service->find($slug, $this->isAdminPagePreview());
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

        return str_starts_with($referer, route(VehicleRouteName::LIST->value));
    }
}

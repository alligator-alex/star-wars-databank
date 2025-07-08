<?php

declare(strict_types=1);

namespace App\Modules\Droid\Public\Controllers;

use App\Modules\Droid\Public\Services\DroidService;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Droid\Public\Requests\FilterRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class DroidController
{
    public function __construct(private readonly DroidService $service)
    {
    }

    public function index(FilterRequest $request): View|JsonResponse
    {
        $droids = $this->service->findPaginated($request);

        /** @phpstan-ignore-next-line */
        $pagination = $droids->withQueryString()
            ->links('public.common.pagination-infinite', ['iconContent' => 'r2']);

        if ($request->ajax()) {
            return response()
                ->json([
                    'html' => [
                        'index' =>  view('public.droids.partials.index-content', [
                            'droids' => $droids,
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
            'models' => $request->getModels(),
            'classes' => $request->getClasses(),
        ];

        return view('public.droids.index', [
            'appliedFilters' => $appliedFilters,
            'appliedFiltersCount' => count(array_filter($appliedFilters)),
            'droids' => $droids,
            'factions' => Faction::dropdownList(columnAsKey: 'slug'),
            'manufacturers' => Manufacturer::dropdownList(columnAsKey: 'slug'),
            'media' => Media::dropdownList(columnAsKey: 'slug'),
            'lines' => HandbookValue::dropdownList(HandbookType::DROID_LINE, 'slug'),
            'models' => HandbookValue::dropdownList(HandbookType::DROID_MODEL, 'slug'),
            'classes' => HandbookValue::dropdownList(HandbookType::DROID_CLASS, 'slug'),
            'pagination' => $pagination,
        ]);
    }

    public function detail(string $slug): View
    {
        $droid = $this->service->findOneBySlug($slug, $this->isAdminPagePreview());
        if (!$droid) {
            abort(404);
        }

        return view('public.droids.detail', [
            'droid' => $droid,
        ]);
    }

    private function isAdminPagePreview(): bool
    {
        $referer = Request::header('referer');
        if (!$referer) {
            return false;
        }

        return str_starts_with($referer, route(DroidRouteName::INDEX->value));
    }
}

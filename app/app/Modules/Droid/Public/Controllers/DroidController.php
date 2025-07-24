<?php

declare(strict_types=1);

namespace App\Modules\Droid\Public\Controllers;

use App\Modules\Droid\Public\Services\DroidService;
use App\Modules\Faction\Public\Services\FactionService;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Public\Services\HandbookValueService;
use App\Modules\Manufacturer\Public\Services\ManufacturerService;
use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Droid\Public\Requests\FilterRequest;
use App\Modules\Media\Public\Services\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class DroidController
{
    public function __construct(
        private readonly DroidService $service,
        private readonly MediaService $mediaService,
        private readonly FactionService $factionService,
        private readonly ManufacturerService $manufacturerService,
        private readonly HandbookValueService $handbookValueService
    ) {
    }

    public function index(FilterRequest $request): View|JsonResponse
    {
        $droids = $this->service->findPaginated($request, $request->getPage());

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
            'factions' => $this->factionService->dropdownList(),
            'manufacturers' => $this->manufacturerService->dropdownList(),
            'media' => $this->mediaService->dropdownList(),
            'lines' => $this->handbookValueService->dropdownList(HandbookType::DROID_LINE),
            'models' => $this->handbookValueService->dropdownList(HandbookType::DROID_MODEL),
            'classes' => $this->handbookValueService->dropdownList(HandbookType::DROID_CLASS),
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

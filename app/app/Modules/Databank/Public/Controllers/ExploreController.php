<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Controllers;

use App\Modules\Core\Common\Controllers\Controller;
use App\Modules\Databank\Public\Enums\ExploreRootType;
use App\Modules\Databank\Public\Services\ExploreService;
use App\Modules\Droid\Public\Services\DroidService;
use App\Modules\Vehicle\Public\Services\VehicleService;
use Illuminate\Contracts\View\View;

class ExploreController extends Controller
{
    public function __construct(
        private readonly ExploreService $exploreService,
        private readonly VehicleService $vehicleService,
        private readonly DroidService $droidService
    ) {
    }

    public function index(string $type, string $slug): View
    {
        $typeEnum = ExploreRootType::tryFrom($type);
        if ($typeEnum === null) {
            abort(404);
        }

        $root = $this->exploreService->findRootModel($typeEnum, $slug);
        if (!$root) {
            abort(404);
        }

        return view('public.explore.index', [
            'type' => $type,
            'root' => $root,
            'models' => $this->exploreService->findRelatedModels($root),
            'randomVehicles' => $this->vehicleService->findRandom(),
            'randomDroids' => $this->droidService->findRandom(),
        ]);
    }
}

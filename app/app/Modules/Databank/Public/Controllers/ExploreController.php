<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Controllers;

use App\Modules\Core\Common\Controllers\Controller;
use App\Modules\Databank\Public\Services\ExploreService;
use Illuminate\Contracts\View\View;

class ExploreController extends Controller
{
    public function __construct(private readonly ExploreService $service)
    {
    }

    public function index(string $type, string $slug): View
    {
        $root = $this->service->findRootModel($type, $slug);
        if (!$root) {
            abort(404);
        }

        return view('public.explore.index', [
            'type' => $type,
            'typeName' => '',
            'root' => $root,
            'models' => $this->service->findRelatedModels($root),
            'randomVehicles' => $this->service->findRandomVehicles(),
            'randomDroids' => $this->service->findRandomDroids(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Controllers;

use App\Modules\Core\Common\Controllers\Controller;
use App\Modules\Databank\Public\Services\FactionService;
use App\Modules\Databank\Public\Services\VehicleService;
use App\Modules\Databank\Public\Services\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cookie;

class HomePageController extends Controller
{
    public function __construct(
        private readonly VehicleService $vehicleService,
        private readonly FactionService $factionService,
        private readonly MediaService $mediaService
    ) {
    }

    public function index(): View
    {
        Cookie::queue(cookie('skip_intro', 'Y', 60 * 24 * 365));

        return view('public.home', [
            'vehiclesCount' => $this->vehicleService->count(),
            'factions' => $this->factionService->findAll(),
            'media' => $this->mediaService->findAll(),
        ]);
    }
}

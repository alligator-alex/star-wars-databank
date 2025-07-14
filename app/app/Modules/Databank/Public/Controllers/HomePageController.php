<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Controllers;

use App\Modules\Core\Common\Controllers\Controller;
use App\Modules\Databank\Common\Enums\CookieName;
use App\Modules\Droid\Public\Services\DroidService;
use App\Modules\Faction\Public\Services\FactionService;
use App\Modules\Media\Public\Services\MediaService;
use App\Modules\Vehicle\Public\Services\VehicleService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cookie;

class HomePageController extends Controller
{
    private const int RANDOM_ENTITIES_COUNT = 15;

    public function __construct(
        private readonly FactionService $factionService,
        private readonly MediaService $mediaService,
        private readonly VehicleService $vehicleService,
        private readonly DroidService $droidService
    ) {
    }

    public function index(): View
    {
        if (!Cookie::has(CookieName::SKIP_INTRO->value)) {
            Cookie::queue(Cookie::make(
                name: CookieName::SKIP_INTRO->value,
                value: 'Y',
                minutes: 60 * 24 * 365,
                httpOnly: false));
        }

        return view('public.home', [
            'skipIntro' => (Cookie::get(CookieName::SKIP_INTRO->value) === 'Y'),
            'vehicles' => $this->vehicleService->findRandom(self::RANDOM_ENTITIES_COUNT),
            'droids' => $this->droidService->findRandom(self::RANDOM_ENTITIES_COUNT),
            'factions' => $this->factionService->findAll(),
            'availableMediaTypes' => $this->mediaService->availableTypes(),
            'media' => $this->mediaService->findAll(),
        ]);
    }
}

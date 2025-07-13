<?php

declare(strict_types=1);

namespace App\Modules\Databank\Console\Commands;

use App\Modules\Databank\Common\Helpers\DescriptionHelper;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;
use Illuminate\Console\Command;

class BeautifyDescription extends Command
{
    protected $signature = 'databank:beautify-description';

    public function __construct(
        private readonly VehicleRepository $vehicleRepository,
        private readonly DroidRepository $droidRepository
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->vehicleRepository->queryBuilder()->lazyById()->each(function (Vehicle $model): void {
            $this->line('Processing Vehicle #' . $model->id . ' - "' . $model->name . '"...');

            DescriptionHelper::beautify($model);
            DescriptionHelper::injectRelatedUrls($model);

            $this->vehicleRepository->save($model);
        });

        $this->droidRepository->queryBuilder()->lazyById()->each(function (Droid $model): void {
            $this->line('Processing Droid #' . $model->id . ' - "' . $model->name . '"...');

            DescriptionHelper::beautify($model);
            DescriptionHelper::injectRelatedUrls($model);

            $this->droidRepository->save($model);
        });
    }
}

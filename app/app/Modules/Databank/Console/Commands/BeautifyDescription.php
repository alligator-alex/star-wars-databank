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
            $this->output->writeln('Processing Vehicle #' . $model->id . ' - "' . $model->name . '"...');

            $model->description = DescriptionHelper::beautify($model->description, $model->name);

            $model->save();
        });

        $this->droidRepository->queryBuilder()->lazyById()->each(function (Droid $model): void {
            $this->output->writeln('Processing Droid #' . $model->id . ' - "' . $model->name . '"...');

            $model->description = DescriptionHelper::beautify($model->description, $model->name);

            $model->save();
        });
    }
}

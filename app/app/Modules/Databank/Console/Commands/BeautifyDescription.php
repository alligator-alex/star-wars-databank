<?php

declare(strict_types=1);

namespace App\Modules\Databank\Console\Commands;

use App\Modules\Databank\Common\Helpers\VehicleHelper;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Repositories\VehicleRepository;
use Illuminate\Console\Command;

class BeautifyDescription extends Command
{
    protected $signature = 'databank:beautify-description';

    public function handle(VehicleRepository $repository): void
    {
        $repository->getQueryBuilder()->each(function (Vehicle $model): void {
            $this->output->writeln('Processing #' . $model->id . ' - "' . $model->name . '"...');

            VehicleHelper::beautifyDescription($model);

            $model->save();
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Databank\Console\Commands;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Models\Line;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Models\VehicleManufacturer;
use Illuminate\Console\Command;

class DisableUnusedRelations extends Command
{
    protected $signature = 'databank:disable-unused-relations';

    public function handle(): void
    {
        $this->disableUnusedLines();
        $this->disableUnusedManufacturers();
    }

    private function disableUnusedLines(): void
    {
        $linesTable = Line::tableName();
        $vehiclesTable = Vehicle::tableName();

        $draftStatus = Status::DRAFT->value;
        $publishedStatus = Status::PUBLISHED->value;

        $sql = "{$linesTable}.id in("
            . "select {$vehiclesTable}.line_id from {$vehiclesTable} where status = {$draftStatus}"
            . " except select {$vehiclesTable}.line_id from {$vehiclesTable} where status = {$publishedStatus}"
        . ")";

        Line::query()->whereRaw($sql)->update([
            'status' => $draftStatus,
        ]);
    }

    private function disableUnusedManufacturers(): void
    {
        $manufacturerTable = Manufacturer::tableName();
        $vehicleManufacturerTable = VehicleManufacturer::tableName();

        $sql =  "{$manufacturerTable}.id not in("
            . "select distinct {$vehicleManufacturerTable}.manufacturer_id from {$vehicleManufacturerTable}"
        . ")";

        Manufacturer::query()->whereRaw($sql)->update([
            'status' => Status::DRAFT->value,
        ]);
    }
}

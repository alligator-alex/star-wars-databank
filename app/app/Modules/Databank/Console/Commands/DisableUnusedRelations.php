<?php

declare(strict_types=1);

namespace App\Modules\Databank\Console\Commands;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Manufacturer\Common\Models\Pivots\Manufacturable;
use Illuminate\Console\Command;

class DisableUnusedRelations extends Command
{
    protected $signature = 'databank:disable-unused-relations';

    public function handle(): void
    {
        $this->disableUnusedManufacturers();
    }

    private function disableUnusedManufacturers(): void
    {
        $manufacturerTable = Manufacturer::tableName();
        $manufacturableTable = Manufacturable::tableName();

        $sql =  "{$manufacturerTable}.id not in("
            . "select distinct {$manufacturableTable}.manufacturer_id from {$manufacturableTable}"
        . ")";

        Manufacturer::query()->whereRaw($sql)->update([
            'status' => Status::DRAFT->value,
        ]);
    }
}

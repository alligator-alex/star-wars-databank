<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\Contracts;

use App\Modules\Databank\Import\DTOs\Vehicle as VehicleDTO;
use App\Modules\Databank\Import\Exceptions\ImporterException;

interface Importer
{
    /**
     * @param iterable<VehicleDTO> $items
     * @param bool $skipExisting
     *
     * @return void
     *
     * @throws ImporterException
     */
    public function import(iterable $items, bool $skipExisting = false): void;
}

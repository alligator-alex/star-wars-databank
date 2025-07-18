<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\Contracts;

use App\Modules\Databank\Common\Contracts\Filterable;

interface VehicleFilter extends Filterable
{
    /** @return string[] */
    public function getFactions(): array;

    /** @return string[] */
    public function getManufacturers(): array;

    /** @return string[] */
    public function getMedia(): array;

    /** @return string[] */
    public function getCategories(): array;

    /** @return string[] */
    public function getTypes(): array;

    /** @return string[] */
    public function getLines(): array;
}

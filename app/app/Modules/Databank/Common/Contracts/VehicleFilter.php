<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Contracts;

interface VehicleFilter
{
    /** @return string[] */
    public function getFactions(): array;

    /** @return string[] */
    public function getManufacturers(): array;

    /** @return string[] */
    public function getMedia(): array;

    /** @return string[] */
    public function getLines(): array;

    /** @return string[] */
    public function getCategories(): array;

    /** @return string[] */
    public function getTypes(): array;
}

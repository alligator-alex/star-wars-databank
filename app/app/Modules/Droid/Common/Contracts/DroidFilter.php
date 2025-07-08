<?php

declare(strict_types=1);

namespace App\Modules\Droid\Common\Contracts;

interface DroidFilter
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
    public function getModels(): array;

    /** @return string[] */
    public function getClasses(): array;
}

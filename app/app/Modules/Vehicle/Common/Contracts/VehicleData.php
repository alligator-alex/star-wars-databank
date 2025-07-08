<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\Contracts;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\CategorySpecificTechSpecs;

interface VehicleData
{
    public function getName(): string;
    public function getSlug(): ?string;
    public function getStatus(): Status;
    public function getSort(): ?int;
    public function getExternalUrl(): ?string;

    public function getCategoryId(): ?int;
    public function getTypeId(): ?int;
    public function getLineId(): ?int;

    /**
     * @return int[]
     */
    public function getManufacturersIds(): array;

    /**
     * @return int[]
     */
    public function getFactionsIds(): array;
    public function getMainFactionId(): ?int;

    public function getImageId(): ?int;
    public function getDescription(): ?string;

    public function getTechSpecs(): ?CategorySpecificTechSpecs;

    /**
     * @return int[]
     */
    public function getAppearancesIds(): array;
}

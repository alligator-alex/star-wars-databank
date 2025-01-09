<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Contracts;

use App\Modules\Databank\Common\DTOs\CategorySpecificTechSpecs;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Enums\VehicleType;

interface VehicleData
{
    public function getName(): string;
    public function getSlug(): ?string;
    public function getStatus(): Status;
    public function getSort(): ?int;
    public function getExternalUrl(): ?string;

    public function getCategory(): ?VehicleCategory;
    public function getType(): ?VehicleType;
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

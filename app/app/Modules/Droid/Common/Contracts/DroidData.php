<?php

declare(strict_types=1);

namespace App\Modules\Droid\Common\Contracts;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Droid\Common\DTOs\TechSpecs;

interface DroidData
{
    public function getName(): string;
    public function getSlug(): ?string;
    public function getStatus(): Status;
    public function getSort(): ?int;
    public function getExternalUrl(): ?string;

    public function getLineId(): ?int;
    public function getModelId(): ?int;
    public function getClassId(): ?int;

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

    public function getTechSpecs(): ?TechSpecs;

    /**
     * @return int[]
     */
    public function getAppearancesIds(): array;
}

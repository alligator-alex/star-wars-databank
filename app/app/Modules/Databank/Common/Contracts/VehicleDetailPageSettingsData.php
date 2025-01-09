<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Contracts;

interface VehicleDetailPageSettingsData
{
    public function getImageOffsetX(): int;
    public function getImageOffsetY(): int;
    public function getImageMaxHeight(): int;
}

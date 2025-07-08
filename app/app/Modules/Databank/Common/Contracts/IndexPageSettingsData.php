<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Contracts;

interface IndexPageSettingsData
{
    public function isCardLarge(): bool;
    public function isImageCovered(): bool;
    public function isImageScaled(): bool;
    public function getImageScale(): float;
    public function isImageOffsetted(): bool;
    public function getImageOffsetX(): int;
    public function getImageOffsetY(): int;
}

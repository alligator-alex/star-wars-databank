<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Requests;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Databank\Common\Contracts\IndexPageSettingsData;

/**
 * @property-read bool|null $cardLarge
 * @property-read bool|null $imageCovered
 * @property-read bool|null $imageScaled
 * @property-read string|null $imageScale
 * @property-read bool|null $imageOffsetted
 * @property-read string|null $imageOffsetX
 * @property-read string|null $imageOffsetY
 */
class UpdateIndexPageSettings extends AdminFormRequest implements IndexPageSettingsData
{
    public function isCardLarge(): bool
    {
        return (bool) $this->cardLarge;
    }

    public function isImageCovered(): bool
    {
        return (bool) $this->imageCovered;
    }

    public function isImageScaled(): bool
    {
        return (bool) $this->imageScaled;
    }

    public function getImageScale(): float
    {
        $value = (float) $this->imageScale;

        if ($value < 0.2) {
            return 0.2;
        }

        if ($value > 1.8) {
            return 1.8;
        }

        return $value;
    }

    public function isImageOffsetted(): bool
    {
        return (bool) $this->imageOffsetted;
    }

    public function getImageOffsetX(): int
    {
        $value = (int) $this->imageOffsetX;

        if ($value < -500) {
            return -500;
        }

        if ($value > 500) {
            return 500;
        }

        return $value;
    }

    public function getImageOffsetY(): int
    {
        $value = (int) $this->imageOffsetY;

        if ($value < -500) {
            return -500;
        }

        if ($value > 500) {
            return 500;
        }

        return $value;
    }
}

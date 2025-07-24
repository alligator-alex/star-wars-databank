<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Requests;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Databank\Common\Contracts\IndexPageSettingsData;

class UpdateIndexPageSettingsRequest extends AdminFormRequest implements IndexPageSettingsData
{
    public function isCardLarge(): bool
    {
        return $this->boolean('cardLarge');
    }

    public function isImageCovered(): bool
    {
        return $this->boolean('imageCovered');
    }

    public function isImageScaled(): bool
    {
        return $this->boolean('imageScaled');
    }

    public function getImageScale(): float
    {
        $value = $this->float('imageScale');

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
        return $this->boolean('imageOffsetted');
    }

    public function getImageOffsetX(): int
    {
        $value = $this->integer('imageOffsetX');

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
        $value = $this->integer('imageOffsetY');

        if ($value < -500) {
            return -500;
        }

        if ($value > 500) {
            return 500;
        }

        return $value;
    }
}

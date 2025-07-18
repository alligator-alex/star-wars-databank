<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Requests;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Databank\Common\Contracts\DetailPageSettingsData;

/**
 * @property-read string|null $imageOffsetX
 * @property-read string|null $imageOffsetY
 * @property-read string|null $imageMaxHeight
 */
class UpdateDetailPageSettingsRequest extends AdminFormRequest implements DetailPageSettingsData
{
    public function getImageOffsetX(): int
    {
        $value = (int) $this->imageOffsetX;

        if ($value < 0) {
            return 0;
        }

        if ($value > 100) {
            return 100;
        }

        return $value;
    }

    public function getImageOffsetY(): int
    {
        $value = (int) $this->imageOffsetY;

        if ($value < 0) {
            return 0;
        }

        if ($value > 100) {
            return 100;
        }

        return $value;
    }

    public function getImageMaxHeight(): int
    {
        $value = (int) $this->imageMaxHeight;

        if ($value < 1) {
            return 1;
        }

        if ($value > 100) {
            return 100;
        }

        return $value;
    }
}

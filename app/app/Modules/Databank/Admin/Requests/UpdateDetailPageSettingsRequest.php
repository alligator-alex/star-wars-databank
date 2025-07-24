<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Requests;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Databank\Common\Contracts\DetailPageSettingsData;

class UpdateDetailPageSettingsRequest extends AdminFormRequest implements DetailPageSettingsData
{
    public function getImageOffsetX(): int
    {
        $value = $this->input('imageOffsetX');

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
        $value = $this->integer('imageOffsetY');

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
        $value = $this->integer('imageMaxHeight');

        if ($value < 1) {
            return 1;
        }

        if ($value > 100) {
            return 100;
        }

        return $value;
    }
}

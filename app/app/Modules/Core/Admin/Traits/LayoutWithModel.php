<?php

declare(strict_types=1);

namespace App\Modules\Core\Admin\Traits;

use App\Modules\Core\Common\Components\Model;

/**
 * @template TModel of Model
 */
trait LayoutWithModel
{
    /**
     * @return TModel
     */
    protected function getModel(): Model
    {
        static $model;

        if (!isset($model)) {
            $model = $this->query->get('model');
        }

        return $model;
    }
}

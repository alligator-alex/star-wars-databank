<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Traits;

trait GetTableName
{
    /**
     * Get model's table name.
     *
     * @return string
     */
    public static function tableName(): string
    {
        return new static()->getTable();
    }
}

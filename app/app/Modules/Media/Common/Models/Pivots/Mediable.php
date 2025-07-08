<?php

declare(strict_types=1);

namespace App\Modules\Media\Common\Models\Pivots;

use App\Modules\Core\Common\Traits\GetTableName;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Mediable extends MorphPivot
{
    use GetTableName;

    public const string RELATION = 'mediable';

    protected $table = 'mediables';
}

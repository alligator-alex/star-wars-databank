<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Common\Models\Pivots;

use App\Modules\Core\Common\Traits\GetTableName;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Manufacturable extends MorphPivot
{
    use GetTableName;

    public const string RELATION = 'manufacturable';

    protected $table = 'manufacturables';
}

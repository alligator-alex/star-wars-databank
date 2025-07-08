<?php

declare(strict_types=1);

namespace App\Modules\Faction\Common\Models\Pivots;

use App\Modules\Core\Common\Traits\GetTableName;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Factionable extends MorphPivot
{
    use GetTableName;

    public const string RELATION = 'factionable';

    protected $table = 'factionables';
}

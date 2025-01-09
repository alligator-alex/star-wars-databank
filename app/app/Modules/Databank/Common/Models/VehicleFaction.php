<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Models;

use App\Modules\Core\Common\Traits\GetTableName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Link of Vehicle with Faction.
 *
 * @property int $id
 * @property int $vehicle_id
 * @property int $faction_id
 * @property bool $main
 * @method static Builder<static>|VehicleFaction newModelQuery()
 * @method static Builder<static>|VehicleFaction newQuery()
 * @method static Builder<static>|VehicleFaction query()
 * @method static Builder<static>|VehicleFaction whereFactionId($value)
 * @method static Builder<static>|VehicleFaction whereId($value)
 * @method static Builder<static>|VehicleFaction whereMain($value)
 * @method static Builder<static>|VehicleFaction whereVehicleId($value)
 */
class VehicleFaction extends Pivot
{
    use GetTableName;

    protected $table = 'vehicle_faction';
}

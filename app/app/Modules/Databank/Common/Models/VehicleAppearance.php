<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Models;

use App\Modules\Core\Common\Traits\GetTableName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Link of Vehicle with Media.
 *
 * @property int $id
 * @property int $vehicle_id
 * @property int $media_id
 * @method static Builder|VehicleAppearance newModelQuery()
 * @method static Builder|VehicleAppearance newQuery()
 * @method static Builder|VehicleAppearance query()
 * @method static Builder|VehicleAppearance whereId($value)
 * @method static Builder|VehicleAppearance whereVehicleId($value)
 * @method static Builder|VehicleAppearance whereMediaId($value)
 */
class VehicleAppearance extends Pivot
{
    use GetTableName;

    protected $table = 'vehicle_appearance';
}

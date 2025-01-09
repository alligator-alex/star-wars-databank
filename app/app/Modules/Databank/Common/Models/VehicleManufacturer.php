<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Models;

use App\Modules\Core\Common\Traits\GetTableName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Link of Vehicle with Manufacturer.
 *
 * @property int $id
 * @property int $vehicle_id
 * @property int $manufacturer_id
 * @method static Builder|VehicleManufacturer newModelQuery()
 * @method static Builder|VehicleManufacturer newQuery()
 * @method static Builder|VehicleManufacturer query()
 * @method static Builder|VehicleManufacturer whereId($value)
 * @method static Builder|VehicleManufacturer whereManufacturerId($value)
 * @method static Builder|VehicleManufacturer whereVehicleId($value)
 */
class VehicleManufacturer extends Pivot
{
    use GetTableName;

    protected $table = 'vehicle_manufacturer';
}

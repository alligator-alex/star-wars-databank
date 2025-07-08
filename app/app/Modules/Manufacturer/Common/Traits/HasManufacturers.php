<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Common\Traits;

use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Manufacturer\Common\Models\Pivots\Manufacturable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasManufacturers
{
    /**
     * Manufacturers.
     *
     * @return MorphToMany<Manufacturer, covariant self>
     */
    public function manufacturers(): MorphToMany
    {
        $table = Manufacturer::tableName();

        return $this->morphToMany(Manufacturer::class, Manufacturable::RELATION)
            ->orderBy($table . '.name')
            ->orderBy($table . '.sort')
            ->orderByDesc($table . '.id');
    }
}

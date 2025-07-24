<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Public\Requests;

use App\Modules\Databank\Public\Requests\BaseIndexRequest;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Rules\HandbookValueExists;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Common\Contracts\VehicleFilter;
use Illuminate\Validation\Rules\Exists;

class FilterRequest extends BaseIndexRequest implements VehicleFilter
{
    protected function getRules(): array
    {
        return [
            'factions' => ['nullable', 'array'],
            'factions.*' => ['required', 'string', new Exists(Faction::tableName(), 'slug')],

            'manufacturers' => ['nullable', 'array'],
            'manufacturers.*' => ['required', 'string', new Exists(Manufacturer::tableName(), 'slug')],

            'media' => ['nullable', 'array'],
            'media.*' => ['required', 'string', new Exists(Media::tableName(), 'slug')],

            'categories' => ['nullable', 'array'],
            'categories.*' => ['required', 'string', new HandbookValueExists(HandbookType::VEHICLE_CATEGORY, 'slug')],

            'types' => ['nullable', 'array'],
            'types.*' => ['required', 'string', new HandbookValueExists(HandbookType::VEHICLE_TYPE, 'slug')],

            'lines' => ['nullable', 'array'],
            'lines.*' => ['required', 'string', new HandbookValueExists(HandbookType::VEHICLE_LINE, 'slug')],
        ];
    }

    public function getFactions(): array
    {
        return $this->array('factions');
    }

    public function getManufacturers(): array
    {
        return $this->array('manufacturers');
    }

    public function getMedia(): array
    {
        return $this->array('media');
    }

    public function getCategories(): array
    {
        return $this->array('categories');
    }

    public function getTypes(): array
    {
        return $this->array('types');
    }

    public function getLines(): array
    {
        return $this->array('lines');
    }
}

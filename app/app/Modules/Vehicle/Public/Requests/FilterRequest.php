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

/**
 * @property-read string[]|null $factions
 * @property-read string[]|null $manufacturers
 * @property-read string[]|null $media
 * @property-read string[]|null $categories
 * @property-read string[]|null $types
 * @property-read string[]|null $lines
 */
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
        return (array) $this->factions;
    }

    public function getManufacturers(): array
    {
        return (array) $this->manufacturers;
    }

    public function getMedia(): array
    {
        return (array) $this->media;
    }

    public function getCategories(): array
    {
        return (array) $this->categories;
    }

    public function getTypes(): array
    {
        return (array) $this->types;
    }

    public function getLines(): array
    {
        return (array) $this->lines;
    }
}

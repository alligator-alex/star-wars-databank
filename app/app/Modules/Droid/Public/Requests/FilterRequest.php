<?php

declare(strict_types=1);

namespace App\Modules\Droid\Public\Requests;

use App\Modules\Databank\Public\Requests\BaseIndexRequest;
use App\Modules\Droid\Common\Contracts\DroidFilter;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Rules\HandbookValueExists;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use Illuminate\Validation\Rules\Exists;

class FilterRequest extends BaseIndexRequest implements DroidFilter
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

            'lines' => ['nullable', 'array'],
            'lines.*' => ['required', 'string', new HandbookValueExists(HandbookType::DROID_LINE, 'slug')],

            'models' => ['nullable', 'array'],
            'models.*' => ['required', 'string', new HandbookValueExists(HandbookType::DROID_MODEL, 'slug')],

            'classes' => ['nullable', 'array'],
            'classes.*' => ['required', 'string', new HandbookValueExists(HandbookType::DROID_CLASS, 'slug')],
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

    public function getLines(): array
    {
        return $this->array('lines');
    }

    public function getModels(): array
    {
        return $this->array('models');
    }

    public function getClasses(): array
    {
        return $this->array('classes');
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Requests;

use App\Modules\Core\Common\Requests\BaseGetList;
use App\Modules\Databank\Common\Contracts\VehicleFilter;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Common\Enums\VehicleType;
use App\Modules\Databank\Common\Models\Faction;
use App\Modules\Databank\Common\Models\Line;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Models\Media;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\In;

/**
 * @property-read string[]|null $faction
 * @property-read string[]|null $manufacturer
 * @property-read string[]|null $media
 * @property-read string[]|null $line
 * @property-read string[]|null $category
 * @property-read string[]|null $type
 */
class GetList extends BaseGetList implements VehicleFilter
{
    public function getFactions(): array
    {
        return (array) $this->faction;
    }

    public function getManufacturers(): array
    {
        return (array) $this->manufacturer;
    }

    public function getMedia(): array
    {
        return (array) $this->media;
    }

    public function getLines(): array
    {
        return (array) $this->line;
    }

    public function getCategories(): array
    {
        return (array) $this->category;
    }

    public function getTypes(): array
    {
        return (array) $this->type;
    }

    protected function getRules(): array
    {
        return [
            'faction' => ['nullable', 'array'],
            'faction.*' => ['required', 'string', new Exists(Faction::tableName(), 'slug')],

            'manufacturer' => ['nullable', 'array'],
            'manufacturer.*' => ['required', 'string', new Exists(Manufacturer::tableName(), 'slug')],

            'media' => ['nullable', 'array'],
            'media.*' => ['required', 'string', new Exists(Media::tableName(), 'slug')],

            'line' => ['nullable', 'array'],
            'line.*' => ['required', 'string', new Exists(Line::tableName(), 'slug')],

            'category' => ['nullable', 'array'],
            'category.*' => [
                'required',
                'string',
                new In(
                    Arr::map(
                        VehicleCategory::cases(),
                        static fn (VehicleCategory $category): string => Str::slug($category->nameForHumans())
                    )
                ),
            ],

            'type' => ['nullable', 'array'],
            'type.*' => [
                'required',
                'string',
                new In(
                    Arr::map(
                        VehicleType::cases(),
                        static fn (VehicleType $type): string => Str::slug($type->nameForHumans())
                    )
                ),
            ],
        ];
    }
}

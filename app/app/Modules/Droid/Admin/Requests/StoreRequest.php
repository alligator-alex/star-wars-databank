<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Requests;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Droid\Common\Contracts\DroidData;
use App\Modules\Droid\Common\DTOs\TechSpecs;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Rules\HandbookValueExists;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

/**
 * @property-read string $name
 * @property-read string|null $slug
 * @property-read string $status
 * @property-read string|null $sort
 * @property-read string $externalUrl
 * @property-read string|null $lineId
 * @property-read string|null $modelId
 * @property-read string|null $classId
 * @property-read string[]|null $manufacturersIds
 * @property-read string[]|null $factionsIds
 * @property-read string|null $mainFactionId
 * @property-read string $imageId
 * @property-read string $description
 * @property-read array|null $techSpecs
 * @property-read string[] $appearancesIds
 */
class StoreRequest extends AdminFormRequest implements DroidData
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', new Unique(Droid::tableName(), 'slug')],
            'status' => ['required', 'integer', new Enum(Status::class)],
            'sort' => ['nullable', 'integer'],
            'externalUrl' => ['required', 'string', 'max:255'],

            'lineId' => ['nullable', 'integer', new HandbookValueExists(HandbookType::DROID_LINE)],
            'modelId' => ['nullable', 'integer', new HandbookValueExists(HandbookType::DROID_MODEL)],
            'classId' => ['nullable', 'integer', new HandbookValueExists(HandbookType::DROID_CLASS)],

            'manufacturersIds' => ['nullable', 'array'],
            'manufacturersIds.*' => ['required', 'integer', new Exists(Manufacturer::tableName(), 'id')],

            'factionsIds' => ['nullable', 'array'],
            'factionsIds.*' => ['required', 'integer', new Exists(Faction::tableName(), 'id')],
            'mainFactionId' => ['nullable', 'integer', new Exists(Faction::tableName(), 'id')],

            'imageId' => ['required', 'integer', new Exists(Attachment::tableName(), 'id')],
            'description' => ['required', 'string', 'max:4096'],

            'techSpecs' => ['nullable', 'array'],

            'appearancesIds' => ['nullable', 'array'],
            'appearancesIds.*' => ['required', 'integer', new Exists(Media::tableName(), 'id')],
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getStatus(): Status
    {
        return Status::tryFrom((int) $this->status);
    }

    public function getSort(): ?int
    {
        return isset($this->sort) ? (int) $this->sort : null;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function getLineId(): ?int
    {
        return (int) $this->lineId ?: null;
    }

    public function getModelId(): ?int
    {
        return (int) $this->modelId ?: null;
    }

    public function getClassId(): ?int
    {
        return (int) $this->classId ?: null;
    }

    public function getManufacturersIds(): array
    {
        if (!$this->manufacturersIds) {
            return [];
        }

        return Arr::map($this->manufacturersIds, static fn (string $id): int => (int) $id);
    }

    public function getFactionsIds(): array
    {
        if (!$this->factionsIds) {
            return [];
        }

        return Arr::map($this->factionsIds, static fn (string $id): int => (int) $id);
    }

    public function getMainFactionId(): ?int
    {
        return (int) $this->mainFactionId ?: null;
    }

    public function getImageId(): ?int
    {
        return (int) $this->imageId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTechSpecs(): ?TechSpecs
    {
        if (!$this->techSpecs) {
            return null;
        }

        return TechSpecs::hydrate((array) $this->techSpecs);
    }

    public function getAppearancesIds(): array
    {
        if (!$this->appearancesIds) {
            return [];
        }

        return Arr::map($this->appearancesIds, static fn (string $id): int => (int) $id);
    }
}

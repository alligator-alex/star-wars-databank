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
        return $this->input('name');
    }

    public function getSlug(): ?string
    {
        return $this->input('slug');
    }

    public function getStatus(): Status
    {
        return $this->enum('status', Status::class);
    }

    public function getSort(): ?int
    {
        return $this->filled('sort') ? $this->integer('sort') : null;
    }

    public function getExternalUrl(): ?string
    {
        return $this->input('externalUrl');
    }

    public function getLineId(): ?int
    {
        return $this->filled('lineId') ? $this->integer('lineId') : null;
    }

    public function getModelId(): ?int
    {
        return $this->filled('modelId') ? $this->integer('modelId') : null;
    }

    public function getClassId(): ?int
    {
        return $this->filled('classId') ? $this->integer('classId') : null;
    }

    public function getManufacturersIds(): array
    {
        if ($this->isNotFilled('manufacturersIds')) {
            return [];
        }

        return Arr::map($this->array('manufacturersIds'), static fn (string $id): int => (int) $id);
    }

    public function getFactionsIds(): array
    {
        if ($this->isNotFilled('factionsIds')) {
            return [];
        }

        return Arr::map($this->array('factionsIds'), static fn (string $id): int => (int) $id);
    }

    public function getMainFactionId(): ?int
    {
        return $this->filled('mainFactionId') ? $this->integer('mainFactionId') : null;
    }

    public function getImageId(): ?int
    {
        return $this->filled('imageId') ? $this->integer('imageId') : null;
    }

    public function getDescription(): ?string
    {
        return $this->input('description');
    }

    public function getTechSpecs(): ?TechSpecs
    {
        if ($this->isNotFilled('techSpecs')) {
            return null;
        }

        return TechSpecs::hydrate($this->array('techSpecs'));
    }

    public function getAppearancesIds(): array
    {
        if ($this->isNotFilled('appearancesIds')) {
            return [];
        }

        return Arr::map($this->array('appearancesIds'), static fn (string $id): int => (int) $id);
    }
}

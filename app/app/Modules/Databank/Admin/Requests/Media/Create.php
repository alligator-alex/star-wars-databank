<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Requests\Media;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Databank\Common\Contracts\MediaData;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Enums\MediaType;
use App\Modules\Databank\Common\Models\Media;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

/**
 * @property-read string $name
 * @property-read string|null $slug
 * @property-read string $status
 * @property-read string|null $sort
 * @property-read string $type
 * @property-read string $releaseDate
 * @property-read string $posterId
 */
class Create extends AdminFormRequest implements MediaData
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', new Unique(Media::tableName(), 'slug')],
            'status' => ['required', 'integer', new Enum(Status::class)],
            'sort' => ['nullable', 'integer'],

            'type' => ['required', 'integer', new Enum(MediaType::class)],
            'releaseDate' => ['required', 'string', 'date_format:Y-m-d'],
            'posterId' => ['required', 'integer', new Exists(Attachment::tableName(), 'id')],
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

    public function getType(): ?MediaType
    {
        return MediaType::tryFrom((int) $this->type);
    }

    public function getReleaseDate(): ?Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->releaseDate);
    }

    public function getPosterId(): int
    {
        return (int) $this->posterId;
    }
}

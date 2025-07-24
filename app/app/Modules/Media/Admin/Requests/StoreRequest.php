<?php

declare(strict_types=1);

namespace App\Modules\Media\Admin\Requests;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Media\Common\Contracts\MediaData;
use App\Modules\Media\Common\Enums\MediaType;
use App\Modules\Media\Common\Models\Media;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

class StoreRequest extends AdminFormRequest implements MediaData
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

    public function getType(): ?MediaType
    {
        return $this->enum('type', MediaType::class);
    }

    public function getReleaseDate(): ?Carbon
    {
        return $this->date('releaseDate', 'Y-m-d');
    }

    public function getPosterId(): int
    {
        return $this->integer('posterId');
    }
}

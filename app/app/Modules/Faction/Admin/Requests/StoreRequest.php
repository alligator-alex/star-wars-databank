<?php

declare(strict_types=1);

namespace App\Modules\Faction\Admin\Requests;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Faction\Common\Contracts\FactionData;
use App\Modules\Faction\Common\Models\Faction;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Unique;

/**
 * @property-read string $name
 * @property-read string|null $slug
 * @property-read string $status
 * @property-read string|null $sort
 */
class StoreRequest extends AdminFormRequest implements FactionData
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', new Unique(Faction::tableName(), 'slug')],
            'status' => ['required', 'integer', new Enum(Status::class)],
            'sort' => ['nullable', 'integer'],
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
}

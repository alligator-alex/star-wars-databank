<?php

declare(strict_types=1);

namespace App\Modules\Faction\Admin\Requests;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Faction\Common\Contracts\FactionData;
use App\Modules\Faction\Common\Models\Faction;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Unique;

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
}

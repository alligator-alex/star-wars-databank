<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Requests\Faction;

use App\Modules\Databank\Common\Models\Faction;
use Illuminate\Validation\Rules\Unique;

class Update extends Create
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'slug' => [
                'nullable',
                'string',
                'max:255',
                new Unique(Faction::tableName(), 'slug')->ignore((int) $this->route()?->parameter('id')),
            ],
        ]);
    }
}

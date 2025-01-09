<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Requests\Line;

use App\Modules\Databank\Common\Models\Line;
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
                new Unique(Line::tableName(), 'slug')->ignore((int) $this->route()?->parameter('id')),
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Requests;

use App\Modules\Droid\Common\Models\Droid;
use Illuminate\Validation\Rules\Unique;

class UpdateRequest extends StoreRequest
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
                new Unique(Droid::tableName(), 'slug')->ignore((int) $this->route()?->parameter('id')),
            ],
        ]);
    }
}

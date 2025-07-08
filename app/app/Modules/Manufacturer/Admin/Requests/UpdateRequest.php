<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Admin\Requests;

use App\Modules\Manufacturer\Common\Models\Manufacturer;
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
                new Unique(Manufacturer::tableName(), 'slug')->ignore((int) $this->route()?->parameter('id')),
            ],
        ]);
    }
}

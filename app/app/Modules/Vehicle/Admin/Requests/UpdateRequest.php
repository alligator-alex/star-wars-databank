<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Requests;

use App\Modules\Vehicle\Common\Models\Vehicle;
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
                new Unique(Vehicle::tableName(), 'slug')->ignore((int) $this->route()?->parameter('id')),
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Media\Admin\Requests;

use App\Modules\Media\Common\Models\Media;
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
                new Unique(Media::tableName(), 'slug')->ignore((int) $this->route()?->parameter('id')),
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Admin\Requests;

use App\Modules\Handbook\Common\Models\HandbookValue;
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
                new Unique(HandbookValue::tableName(), 'slug')
                    ->where('handbook_id', (int) $this->route()?->parameter('handbookId'))
                    ->ignore((int) $this->route()?->parameter('handbookValueId'), 'id')
            ],
        ]);
    }
}

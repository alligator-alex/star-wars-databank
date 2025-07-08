<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Admin\Requests;

use App\Modules\Core\Admin\Requests\AdminFormRequest;
use App\Modules\Handbook\Common\Contracts\HandbookValueData;
use App\Modules\Handbook\Common\Models\HandbookValue;
use Illuminate\Validation\Rules\Unique;

/**
 * @property-read string $name
 * @property-read string|null $slug
 */
class StoreRequest extends AdminFormRequest implements HandbookValueData
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                new Unique(HandbookValue::tableName(), 'slug')->where('handbook_id', $this->route('handbookId'))
            ],
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
}

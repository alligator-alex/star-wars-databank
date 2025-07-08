<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string|null $page
 */
abstract class BaseIndexRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    abstract protected function getRules(): array;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return array_merge($this->getRules(), [
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    public function getPage(): int
    {
        return (int) $this->page;
    }
}

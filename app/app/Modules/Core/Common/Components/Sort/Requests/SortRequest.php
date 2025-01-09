<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Components\Sort\Requests;

use App\Modules\Core\Common\Components\Sort\Contracts\Sort;
use App\Modules\Core\Common\Components\Sort\Enums\SortDirection;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string|null $sort
 */
class SortRequest extends FormRequest implements Sort
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'sort' => ['nullable', 'string'],
        ];
    }

    public function getAttribute(): ?string
    {
        if (!isset($this->sort)) {
            return null;
        }

        return str_starts_with($this->sort, '-')
            ? str_replace('-', '', $this->sort)
            : $this->sort;
    }

    public function getDirection(): ?SortDirection
    {
        if (!isset($this->sort)) {
            return null;
        }

        return str_starts_with($this->sort, '-')
            ? SortDirection::DESC
            : SortDirection::ASC;
    }
}

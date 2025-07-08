<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Components\Filters;

use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Handbook\Common\Rules\HandbookValueExists;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CategoryFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['categoriesIds'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'categoriesIds' => ['nullable', 'array'],
            'categoriesIds.*' => ['nullable', 'integer', new HandbookValueExists(HandbookType::VEHICLE_CATEGORY)],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereIn('category_id', (array) $this->request->get('categoriesIds'));
    }

    /**
     * @return array<int, Field>
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function display(): array
    {
        return [
            Select::make('categoriesIds.')
                ->title(__('Category'))
                ->multiple()
                ->options(HandbookValue::dropdownList(HandbookType::VEHICLE_CATEGORY))
                ->value((array) $this->request->get('categoriesIds')),
        ];
    }
}

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

class TypeFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['typesIds'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'typesIds' => ['nullable', 'array'],
            'typesIds.*' => ['nullable', 'integer', new HandbookValueExists(HandbookType::VEHICLE_TYPE)],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereIn('type_id', (array) $this->request->get('typesIds'));
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
            Select::make('typesIds.')
                ->title(__('Type'))
                ->multiple()
                ->options(HandbookValue::dropdownList(HandbookType::VEHICLE_TYPE))
                ->value((array) $this->request->get('typesIds')),
        ];
    }
}

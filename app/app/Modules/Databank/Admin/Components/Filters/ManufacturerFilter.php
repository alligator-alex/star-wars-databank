<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Filters;

use App\Modules\Manufacturer\Common\Models\Manufacturer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Exists;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ManufacturerFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['manufacturersIds'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'manufacturersIds' => ['nullable', 'array'],
            'manufacturersIds.*' => ['nullable', 'integer', new Exists(Manufacturer::tableName(), 'id')],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereHas('manufacturers', function (Builder $subQuery): void {
            /** @var Builder|Manufacturer $subQuery */
            /** @phpstan-ignore-next-line */
            $subQuery->withDrafts();

            $subQuery->whereIn($subQuery->getModel()->getTable() . '.id', (array) $this->request->get('manufacturersIds'));
        });
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
            Select::make('manufacturersIds.')
                ->title(__('Manufacturer'))
                ->options(Manufacturer::dropdownList(true))
                ->value((array) $this->request->get('manufacturersIds'))
                ->multiple(),
        ];
    }
}

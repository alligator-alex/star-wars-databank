<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Filters;

use App\Modules\Databank\Common\Models\Manufacturer;
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
    public $parameters = ['manufacturer'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'manufacturer' => ['nullable', 'array'],
            'manufacturer.*' => ['nullable', 'integer', new Exists(Manufacturer::tableName(), 'id')],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereHas('manufacturers', function (Builder $subQuery): void {
            /** @var Builder|Manufacturer $subQuery */
            /** @phpstan-ignore-next-line */
            $subQuery->withDrafts();

            $subQuery->whereIn($subQuery->getModel()->getTable() . '.id', (array) $this->request->get('manufacturer'));
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
            Select::make('manufacturer')
                ->title(__('Manufacturer'))
                ->options(Manufacturer::dropdownList(true))
                ->value((array) $this->request->get('manufacturer'))
                ->multiple(),
        ];
    }
}

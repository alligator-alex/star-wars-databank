<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Filters;

use App\Modules\Databank\Common\Models\Line;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Exists;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class LineFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['line'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'line' => ['nullable', 'array'],
            'line.*' => ['nullable', 'integer', new Exists(Line::tableName(), 'id')],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereHas('line', function (Builder $subQuery): void {
            /** @var Builder|Line $subQuery */
            /** @phpstan-ignore-next-line */
            $subQuery->withDrafts();

            $subQuery->whereIn($subQuery->getModel()->getTable() . '.id', (array) $this->request->get('line'));
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
            Select::make('line')
                ->title(__('Line'))
                ->options(Line::dropdownList(true))
                ->value((array) $this->request->get('line'))
                ->multiple(),
        ];
    }
}

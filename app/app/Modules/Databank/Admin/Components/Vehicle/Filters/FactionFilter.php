<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Filters;

use App\Modules\Databank\Common\Models\Faction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Exists;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FactionFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['faction'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'faction' => ['nullable', 'array'],
            'faction.*' => ['nullable', 'integer', new Exists(Faction::tableName(), 'id')],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereHas('factions', function (Builder $subQuery): void {
            /** @var Builder|Faction $subQuery */
            /** @phpstan-ignore-next-line */
            $subQuery->withDrafts();

            $subQuery->whereIn($subQuery->getModel()->getTable() . '.id', (array) $this->request->get('faction'));
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
            Select::make('faction')
                ->title(__('Faction'))
                ->options(Faction::dropdownList(true))
                ->value((array) $this->request->get('faction'))
                ->multiple(),
        ];
    }
}

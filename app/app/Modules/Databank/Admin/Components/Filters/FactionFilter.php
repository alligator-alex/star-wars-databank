<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Filters;

use App\Modules\Faction\Common\Models\Faction;
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
    public $parameters = ['factionsIds'];

    public function __construct(private readonly array $dropdownList)
    {
        parent::__construct();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'factionsIds' => ['nullable', 'array'],
            'factionsIds.*' => ['nullable', 'integer', new Exists(Faction::tableName(), 'id')],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereHas('factions', function (Builder $subQuery): void {
            /** @var Builder|Faction $subQuery */
            /** @phpstan-ignore-next-line */
            $subQuery->withDrafts();

            $subQuery->whereIn($subQuery->getModel()->getTable() . '.id', (array) $this->request->get('factionsIds'));
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
            Select::make('factionsIds.')
                ->title(__('Faction'))
                ->options($this->dropdownList)
                ->value((array) $this->request->get('factionsIds'))
                ->multiple(),
        ];
    }
}

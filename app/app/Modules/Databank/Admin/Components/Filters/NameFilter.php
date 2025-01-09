<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class NameFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['name'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->where('name', 'ilike', '%' . $this->request->get('name') . '%');
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
            Input::make('name')
                ->title(__('Name'))
                ->value($this->request->get('name')),
        ];
    }
}

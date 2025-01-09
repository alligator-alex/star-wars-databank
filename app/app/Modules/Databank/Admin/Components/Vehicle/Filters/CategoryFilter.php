<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Filters;

use App\Modules\Databank\Common\Enums\VehicleCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Enum;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class CategoryFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['category'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'array'],
            'category.*' => ['nullable', 'integer', new Enum(VehicleCategory::class)],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereIn('category', (array) $this->request->get('category'));
    }

    /**
     * @return array<int, Field>
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function display(): array
    {
        return [
            Select::make('category')
                ->title(__('Category'))
                ->fromEnum(VehicleCategory::class, 'nameForHumans')
                ->value((array) $this->request->get('category'))
                ->multiple(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Vehicle\Filters;

use App\Modules\Databank\Common\Enums\VehicleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Enum;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class TypeFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['type'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', 'array'],
            'type.*' => ['nullable', 'integer', new Enum(VehicleType::class)],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereIn('type', (array) $this->request->get('type'));
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
            Select::make('type')
                ->title(__('Type'))
                ->fromEnum(VehicleType::class, 'nameForHumans')
                ->value((array) $this->request->get('type'))
                ->multiple(),
        ];
    }
}

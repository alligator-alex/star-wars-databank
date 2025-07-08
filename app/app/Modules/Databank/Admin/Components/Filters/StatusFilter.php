<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Filters;

use App\Modules\Databank\Common\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Enum;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class StatusFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['status'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'integer', new Enum(Status::class)],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->where('status', '=', (int) $this->request->get('status'));
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
            Select::make('status')
                ->title(__('Status'))
                ->fromEnum(Status::class, 'nameForHumans')
                ->empty(__('Any'))
                ->set('placeholder', __('Any'))
                ->value($this->request->has('status') ? (int) $this->request->get('status') : null),
        ];
    }
}

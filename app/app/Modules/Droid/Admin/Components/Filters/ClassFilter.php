<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Components\Filters;

use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Rules\HandbookValueExists;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ClassFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['classesIds'];

    /**
     * @param array<string, string> $dropdownList
     */
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
            'classesIds' => ['nullable', 'array'],
            'classesIds.*' => ['nullable', 'integer', new HandbookValueExists(HandbookType::DROID_CLASS)],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereIn('class_id', (array) $this->request->get('classesIds'));
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
            Select::make('classesIds.')
                ->title(__('Class'))
                ->multiple()
                ->options($this->dropdownList)
                ->value((array) $this->request->get('classesIds')),
        ];
    }
}

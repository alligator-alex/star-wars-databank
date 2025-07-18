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

class ModelFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['modelsIds'];

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
            'modelsIds' => ['nullable', 'array'],
            'modelsIds.*' => ['nullable', 'integer', new HandbookValueExists(HandbookType::DROID_MODEL)],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereIn('model_id', (array) $this->request->get('modelsIds'));
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
            Select::make('modelsIs.')
                ->title(__('Model'))
                ->multiple()
                ->options($this->dropdownList)
                ->value((array) $this->request->get('modelsIds')),
        ];
    }
}

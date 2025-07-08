<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Components\Filters;

use App\Modules\Media\Common\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Exists;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MediaFilter extends Filter
{
    /**
     * @var array<int, string>
     */
    public $parameters = ['mediaIds'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'mediaIds' => ['nullable', 'array'],
            'mediaIds.*' => ['nullable', 'integer', new Exists(Media::tableName(), 'id')],
        ];
    }

    public function run(Builder $builder): Builder
    {
        $this->request->validate($this->rules());

        return $builder->whereHas('appearances', function (Builder $subQuery): void {
            /** @var Builder|Media $subQuery */
            /** @phpstan-ignore-next-line */
            $subQuery->withDrafts();

            $subQuery->whereIn($subQuery->getModel()->getTable() . '.id', (array) $this->request->get('mediaIds'));
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
            Select::make('mediaIds.')
                ->title(__('Media'))
                ->fromQuery($this->optionsQuery(), 'name', 'id')
                ->value((array) $this->request->get('mediaIds'))
                ->multiple(),
        ];
    }

    private function optionsQuery(): Builder
    {
        /** @var Builder|Media $query */
        $query = Media::query();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        return $query->orderBy('release_date')->orderBy('sort');
    }
}

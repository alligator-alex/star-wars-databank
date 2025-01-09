<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Scopes;

use App\Modules\Core\Common\Components\Model;
use App\Modules\Databank\Common\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Scope;

/**
 * @template TModel of Model
 */
class PublishableScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder<TModel> $builder
     * @param Model $model
     *
     * @return void
     */
    public function apply(Builder $builder, EloquentModel $model): void
    {
        $builder->where('status', '=', Status::PUBLISHED->value);
    }

    public function extend(Builder $builder): void
    {
        $this->addWithDrafts($builder);
        $this->addWithoutDrafts($builder);
        $this->addOnlyDrafts($builder);
    }

    /**
     * Add the with-drafts extension to the builder.
     *
     * @param Builder<TModel> $builder
     *
     * @return void
     */
    protected function addWithDrafts(Builder $builder): void
    {
        $builder->macro('withDrafts', function (Builder $builder): Builder {
            /** @phpstan-var Scope $this */
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-drafts extension to the builder.
     *
     * @param Builder<TModel> $builder
     *
     * @return void
     */
    protected function addWithoutDrafts(Builder $builder): void
    {
        $builder->macro('withoutDrafts', function (Builder $builder): Builder {
            /** @phpstan-var Scope $this */
            $builder->withoutGlobalScope($this)->where('status', '=', Status::PUBLISHED->value);

            return $builder;
        });
    }

    /**
     * Add the only-drafts extension to the builder.
     *
     * @param Builder<TModel> $builder
     *
     * @return void
     */
    protected function addOnlyDrafts(Builder $builder): void
    {
        $builder->macro('onlyDrafts', function (Builder $builder): Builder {
            /** @phpstan-var Scope $this */
            $builder->withoutGlobalScope($this)->where('status', '=', Status::DRAFT->value);

            return $builder;
        });
    }
}

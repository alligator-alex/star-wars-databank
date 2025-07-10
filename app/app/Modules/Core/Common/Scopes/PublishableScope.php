<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Scopes;

use App\Modules\Databank\Common\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PublishableScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
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
     * Add the "withDrafts" extension to the builder.
     */
    protected function addWithDrafts(Builder $builder): void
    {
        $builder->macro('withDrafts', function (Builder $builder): Builder {
            /** @phpstan-var Scope $this */
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the "withoutDrafts" extension to the builder.
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
     * Add the "onlyDrafts" extension to the builder.
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

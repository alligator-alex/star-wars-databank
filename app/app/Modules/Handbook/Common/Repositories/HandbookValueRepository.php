<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Repositories;

use App\Modules\Core\Common\Repositories\ModelRepository;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends ModelRepository<HandbookValue>
 */
class HandbookValueRepository extends ModelRepository
{
    protected string $modelClass = HandbookValue::class;

    public function findOneBySlug(int $handbookId, string $slug): ?HandbookValue
    {
        $query = $this->queryBuilder()
            ->where('handbook_id', '=', $handbookId)
            ->where('slug', '=', $slug);

        /** @var HandbookValue|null $model */
        $model = $query->first();

        return $model;
    }

    public function count(HandbookType $type): int
    {
        return $this->queryBuilder()
            ->whereHas('handbook', static fn (Builder $subQuery): Builder => $subQuery->where('type', '=', $type->value))
            ->count();
    }
}

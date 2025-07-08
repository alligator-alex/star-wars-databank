<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Repositories;

use App\Modules\Core\Common\Repositories\ModelRepository;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\Handbook;

/**
 * @extends ModelRepository<Handbook>
 */
class HandbookRepository extends ModelRepository
{
    protected string $modelClass = Handbook::class;

    public function findOneByType(HandbookType $type): ?Handbook
    {
        $query = $this->queryBuilder()->where('type', '=', $type->value);

        /** @var Handbook|null $model */
        $model = $query->first();

        return $model;
    }
}

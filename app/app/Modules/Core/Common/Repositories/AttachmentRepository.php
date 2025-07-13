<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Repositories;

use App\Modules\Core\Common\Models\Attachment;
use Illuminate\Database\Eloquent\Builder;

class AttachmentRepository
{
    protected string $modelClass = Attachment::class;

    /**
     * Get query builder instance for model.
     *
     * @return Builder<Attachment>
     */
    public function queryBuilder(): Builder
    {
        return $this->modelClass::query();
    }

    /**
     * Find model by ID.
     *
     * @param int $id
     *
     * @return Attachment|null
     */
    public function findById(int $id): ?Attachment
    {
        return $this->queryBuilder()
            ->where('id', '=', $id)
            ->first();
    }
}

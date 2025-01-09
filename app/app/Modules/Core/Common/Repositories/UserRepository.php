<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Repositories;

use App\Modules\Core\Common\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository
{
    protected string $modelClass = User::class;

    /**
     * Get query builder instance for model.
     *
     * @return Builder<User>
     */
    public function getQueryBuilder(): Builder
    {
        return $this->modelClass::query();
    }

    /**
     * Find model by ID.
     *
     * @param int $id
     *
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return $this->getQueryBuilder()
            ->where('id', '=', $id)
            ->first();
    }

    /**
     * Save the model with refresh.
     *
     * @param User $model
     *
     * @return bool
     */
    public function save(mixed $model): bool
    {
        $isSaved = $model->save();
        if ($isSaved) {
            $model->refresh();
        }

        return $isSaved;
    }
}

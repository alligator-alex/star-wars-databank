<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Repositories;

use App\Modules\Core\Common\Components\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Model
 */
abstract class ModelRepository
{
    /** @var class-string<TModel> */
    protected string $modelClass;

    /**
     * Get new model instance.
     *
     * @return TModel
     */
    public function getNewModel(): Model
    {
        return new $this->modelClass();
    }

    /**
     * Get query builder instance for model.
     *
     * @return Builder<TModel>
     */
    public function getQueryBuilder(): Builder
    {
        /** @var Builder<TModel> $query */
        $query = $this->modelClass::query();

        return $query;
    }

    /**
     * Find model by ID.
     *
     * @param int $id
     *
     * @return TModel|null
     */
    public function findById(int $id): ?Model
    {
        return $this->getQueryBuilder()
            ->where('id', '=', $id)
            ->first();
    }

    /**
     * Save the model with refresh.
     *
     * @param TModel $model
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

    /**
     * Delete the model.
     *
     * @param TModel $model
     *
     * @return bool
     */
    public function delete(mixed $model): bool
    {
        return (bool) $model->delete();
    }
}

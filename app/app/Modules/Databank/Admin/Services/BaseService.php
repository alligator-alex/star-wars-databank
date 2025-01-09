<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Core\Common\Components\Model;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Screen\Layouts\Selection;

/**
 * @template TModel of Model
 */
abstract class BaseService
{
    protected const int PER_PAGE = 20;

    protected BaseRepository $repository;

    /**
     * @return TModel
     */
    public function getNewModel(): Model
    {
        return $this->repository->getNewModel();
    }

    /**
     * @return string[]
     */
    abstract protected function relations(): array;

    /**
     * Find all models with pagination.
     *
     * @param Selection|null $filter
     *
     * @return LengthAwarePaginator<TModel>
     */
    public function findAllPaginated(?Selection $filter = null): LengthAwarePaginator
    {
        /** @var Builder<TModel> $query */
        $query = $this->repository->getQueryBuilder();

        /** @phpstan-ignore-next-line */
        $query->withDrafts();

        if (!empty($this->relations())) {
            $query->with($this->relations());
        }

        if ($filter) {
            /** @phpstan-ignore-next-line */
            $query->filters($filter);
        }

        /** @phpstan-ignore-next-line */
        $query->defaultSort('sort', 'asc');
        $query->orderByDesc('id');

        return $query->paginate(static::PER_PAGE);
    }

    /**
     * Find model by ID.
     *
     * @param int $id
     *
     * @return TModel
     *
     * @throws AdminServiceException
     */
    public function find(int $id): Model
    {
        $model = $this->repository->findById($id, true);

        if (!$model) {
            throw new AdminServiceException('Not found');
        }

        if (!empty($this->relations())) {
            $model->load($this->relations());
        }

        return $model;
    }

    /**
     * Toggle model status by ID.
     *
     * @param int $id
     *
     * @return TModel
     *
     * @throws AdminServiceException
     */
    public function togglePublish(int $id): Model
    {
        $model = $this->find($id);

        if (!method_exists($model, 'isPublished')) {
            throw new AdminServiceException($model::class . ' is not an instance of Publishable');
        }

        if (!isset($model->status)) {
            throw new AdminServiceException($model::class . ' does not have a status property');
        }

        if ($model->isPublished()) {
            $model->status = Status::DRAFT;
        } else {
            $model->status = Status::PUBLISHED;
        }

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to ' . ($model->isPublished() ? 'unpublish' : 'publish'));
        }

        return $model;
    }

    /**
     * Delete model by ID.
     *
     * @param int $id
     *
     * @return TModel
     *
     * @throws AdminServiceException
     */
    public function delete(int $id): Model
    {
        $model = $this->find($id);

        if (!$this->repository->delete($model)) {
            throw new AdminServiceException('Unable to delete');
        }

        $model->exists = false;

        return $model;
    }
}

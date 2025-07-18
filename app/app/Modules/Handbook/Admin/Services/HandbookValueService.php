<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Core\Common\Helpers\CacheHelper;
use App\Modules\Handbook\Common\Contracts\HandbookValueData;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Handbook\Public\Enums\CacheKeyPrefix;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Screen\Layouts\Selection;

class HandbookValueService
{
    private const int PER_PAGE = 20;

    public function __construct(private readonly HandbookValueRepository $repository)
    {
    }

    /**
     * Find all models with pagination.
     *
     * @param int $handbookId
     * @param Selection|null $filter
     *
     * @return LengthAwarePaginator<int, HandbookValue>
     */
    public function findPaginated(int $handbookId, ?Selection $filter = null): LengthAwarePaginator
    {
        /** @var Builder<HandbookValue> $query */
        $query = $this->repository->queryBuilder()
            ->where('handbook_id', '=', $handbookId)
            ->with(['handbook']);

        if ($filter) {
            /** @phpstan-ignore-next-line */
            $query->filters($filter);
        }

        $query->orderByDesc('id');

        return $query->paginate(self::PER_PAGE);
    }

    /**
     * Find model by ID.
     *
     * @param int $handbookId
     * @param int $handbookValueId
     *
     * @return HandbookValue
     *
     * @throws AdminServiceException
     */
    public function findOneByIdOrFail(int $handbookId, int $handbookValueId): HandbookValue
    {
        /** @var HandbookValue|null $model */
        $model = $this->repository->queryBuilder()
            ->where('handbook_id', '=', $handbookId)
            ->where('id', '=', $handbookValueId)
            ->first();

        if (!$model) {
            throw new AdminServiceException('Not found');
        }

        return $model->load(['handbook']);
    }

    /**
     * Create new model.
     *
     * @throws AdminServiceException
     */
    public function create(int $handbookId, HandbookValueData $dto): HandbookValue
    {
        $model = $this->newModel();

        $model->handbook_id = $handbookId;

        return $this->saveWithNewData($model, $dto);
    }

    /**
     * Update existing model.
     *
     * @throws AdminServiceException
     */
    public function update(int $handbookId, int $handbookValueId, HandbookValueData $dto): HandbookValue
    {
        $model = $this->findOneByIdOrFail($handbookId, $handbookValueId);

        return $this->saveWithNewData($model, $dto);
    }

    /**
     * Update model data with DTO and save.
     *
     * @throws AdminServiceException
     */
    private function saveWithNewData(HandbookValue $model, HandbookValueData $dto): HandbookValue
    {
        $model->name = $dto->getName();
        $model->slug = $dto->getSlug();

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to save');
        }

        $this->forgetCache();

        return $model;
    }

    /**
     * @throws AdminServiceException
     */
    public function delete(int $handbookId, int $handbookValueId): HandbookValue
    {
        $model = $this->findOneByIdOrFail($handbookId, $handbookValueId);

        if (!$this->repository->delete($model)) {
            throw new AdminServiceException('Unable to delete');
        }

        $model->exists = false;

        $this->forgetCache();

        return $model;
    }

    /**
     * @return HandbookValue
     */
    public function newModel(): HandbookValue
    {
        return $this->repository->newModel();
    }

    public function dropdownList(HandbookType $type): array
    {
        return $this->repository->dropdownList($type);
    }

    private function forgetCache(): void
    {
        CacheHelper::forgetByWildcard(CacheKeyPrefix::DROPDOWN_LIST);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Common\Contracts\LineData;
use App\Modules\Databank\Common\Models\Line;
use App\Modules\Databank\Common\Repositories\LineRepository;

/**
 * @extends BaseService<Line>
 */
class LineService extends BaseService
{
    public function __construct(LineRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function relations(): array
    {
        return [];
    }

    /**
     * Create new model.
     *
     * @param LineData $dto
     *
     * @return Line
     *
     * @throws AdminServiceException
     */
    public function create(LineData $dto): Line
    {
        /** @var Line $model */
        $model = $this->repository->getNewModel();

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update existing model.
     *
     * @param int $id
     * @param LineData $dto
     *
     * @return Line
     *
     * @throws AdminServiceException
     */
    public function update(int $id, LineData $dto): Line
    {
        $model = $this->find($id);

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update model data with DTO and save.
     *
     * @param Line $model
     * @param LineData $dto
     *
     * @return Line
     *
     * @throws AdminServiceException
     */
    private function updateWithDto(Line $model, LineData $dto): Line
    {
        $model->name = $dto->getName();
        $model->slug = $dto->getSlug();
        $model->status = $dto->getStatus();
        $model->sort = $dto->getSort() ?? 500;

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to save');
        }

        return $model;
    }
}

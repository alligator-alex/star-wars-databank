<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Common\Contracts\ManufacturerData;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Repositories\ManufacturerRepository;

/**
 * @extends BaseService<Manufacturer>
 */
class ManufacturerService extends BaseService
{
    public function __construct(ManufacturerRepository $repository)
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
     * @param ManufacturerData $dto
     *
     * @return Manufacturer
     *
     * @throws AdminServiceException
     */
    public function create(ManufacturerData $dto): Manufacturer
    {
        /** @var Manufacturer $model */
        $model = $this->repository->getNewModel();

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update existing model.
     *
     * @param int $id
     * @param ManufacturerData $dto
     *
     * @return Manufacturer
     *
     * @throws AdminServiceException
     */
    public function update(int $id, ManufacturerData $dto): Manufacturer
    {
        $model = $this->find($id);

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update model data with DTO and save.
     *
     * @param Manufacturer $model
     * @param ManufacturerData $dto
     *
     * @return Manufacturer
     *
     * @throws AdminServiceException
     */
    private function updateWithDto(Manufacturer $model, ManufacturerData $dto): Manufacturer
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

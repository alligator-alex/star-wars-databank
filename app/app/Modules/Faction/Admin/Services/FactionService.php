<?php

declare(strict_types=1);

namespace App\Modules\Faction\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Admin\Services\BaseService;
use App\Modules\Faction\Common\Contracts\FactionData;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Faction\Common\Repositories\FactionRepository;

/**
 * @extends BaseService<Faction>
 */
class FactionService extends BaseService
{
    public function __construct(FactionRepository $repository)
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
     * @param FactionData $dto
     *
     * @return Faction
     *
     * @throws AdminServiceException
     */
    public function create(FactionData $dto): Faction
    {
        /** @var Faction $model */
        $model = $this->repository->newModel();

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update existing model.
     *
     * @param int $id
     * @param FactionData $dto
     *
     * @return Faction
     *
     * @throws AdminServiceException
     */
    public function update(int $id, FactionData $dto): Faction
    {
        $model = $this->findOneById($id);

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update model data with DTO and save.
     *
     * @param Faction $model
     * @param FactionData $dto
     *
     * @return Faction
     *
     * @throws AdminServiceException
     */
    private function updateWithDto(Faction $model, FactionData $dto): Faction
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

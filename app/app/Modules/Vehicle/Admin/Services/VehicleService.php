<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Admin\Services\BaseService;
use App\Modules\Databank\Admin\Traits\ServiceWithPageSettings;
use App\Modules\Vehicle\Common\Contracts\VehicleData;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;

/**
 * @extends BaseService<Vehicle>
 */
class VehicleService extends BaseService
{
    use ServiceWithPageSettings;

    public function __construct(VehicleRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function relations(): array
    {
        return [
            'category',
            'type',
            'line',
            'image',
            'manufacturers',
            'factions',
            'mainFaction',
            'appearances',
        ];
    }

    /**
     * Create new model.
     *
     * @throws AdminServiceException
     */
    public function create(VehicleData $dto): Vehicle
    {
        /** @var Vehicle $model */
        $model = $this->repository->newModel();

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update existing model.
     *
     * @throws AdminServiceException
     */
    public function update(int $id, VehicleData $dto): Vehicle
    {
        $model = $this->findOneById($id);

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update model data with DTO and save.
     *
     * @throws AdminServiceException
     */
    private function updateWithDto(Vehicle $model, VehicleData $dto): Vehicle
    {
        $model->name = $dto->getName();
        $model->slug = $dto->getSlug();
        $model->status = $dto->getStatus();
        $model->sort = $dto->getSort() ?? 500;
        $model->external_url = $dto->getExternalUrl();

        $model->category_id = $dto->getCategoryId();
        $model->type_id = $dto->getTypeId();
        $model->line_id = $dto->getLineId();
        $model->image_id = $dto->getImageId();
        $model->description = $dto->getDescription();

        $model->technical_specifications = $dto->getTechSpecs()?->toArray();

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to save');
        }

        $model->appearances()->sync($dto->getAppearancesIds());

        $model->manufacturers()->sync($dto->getManufacturersIds());

        $factionsSyncData = [];
        foreach ($dto->getFactionsIds() as $id) {
            $factionsSyncData[$id] = [
                'main' => ($id === $dto->getMainFactionId()),
            ];
        }

        $model->factions()->sync($factionsSyncData);

        return $model;
    }
}

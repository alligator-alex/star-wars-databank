<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Common\Contracts\VehicleData;
use App\Modules\Databank\Common\Contracts\VehicleDetailPageSettingsData;
use App\Modules\Databank\Common\Contracts\VehicleListPageSettingsData;
use App\Modules\Databank\Common\DTOs\VehiclePageSettings\DetailPagePageSettings;
use App\Modules\Databank\Common\DTOs\VehiclePageSettings\ListPageSettings;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Repositories\VehicleRepository;

/**
 * @extends BaseService<Vehicle>
 */
class VehicleService extends BaseService
{
    public function __construct(VehicleRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function relations(): array
    {
        return [
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
        $model = $this->repository->getNewModel();

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update existing model.
     *
     * @throws AdminServiceException
     */
    public function update(int $id, VehicleData $dto): Vehicle
    {
        $model = $this->find($id);

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

        $model->category = $dto->getCategory();
        $model->type = $dto->getType();
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

    /**
     * @throws AdminServiceException
     */
    public function updateListPageSettings(int $id, VehicleListPageSettingsData $dto): void
    {
        $model = $this->find($id);

        $listSettings = ListPageSettings::hydrate([
            'cardLarge' => $dto->isCardLarge(),
            'imageCovered' => $dto->isImageCovered(),
            'imageScaled' => $dto->isImageScaled(),
            'imageScale' => $dto->getImageScale(),
            'imageOffsetted' => $dto->isImageOffsetted(),
            'imageOffsetX' => $dto->getImageOffsetX(),
            'imageOffsetY' => $dto->getImageOffsetY(),
        ]);

        $settings = $model->getPageSettings();
        $settings->setForList($listSettings);

        $model->page_settings = $settings->toArray();

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to save');
        }
    }

    /**
     * @throws AdminServiceException
     */
    public function updateDetailPageSettings(int $id, VehicleDetailPageSettingsData $dto): void
    {
        $detailSettings = new DetailPagePageSettings();

        $detailSettings->setImageOffsetX($dto->getImageOffsetX());
        $detailSettings->setImageOffsetY($dto->getImageOffsetY());
        $detailSettings->setImageMaxHeight($dto->getImageMaxHeight());

        $model = $this->find($id);

        $model->page_settings = $model->getPageSettings()
            ->setForDetail($detailSettings)
            ->toArray();

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to save');
        }
    }
}

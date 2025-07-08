<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Traits;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Common\Contracts\DetailPageSettingsData;
use App\Modules\Databank\Common\Contracts\IndexPageSettingsData;
use App\Modules\Databank\Common\DTOs\PageSettings\DetailPagePageSettings;
use App\Modules\Databank\Common\DTOs\PageSettings\IndexPageSettings;

trait ServiceWithPageSettings
{
    /**
     * @throws AdminServiceException
     */
    public function updateIndexPageSettings(int $id, IndexPageSettingsData $dto): void
    {
        $model = $this->findOneById($id);

        $indexSettings = IndexPageSettings::hydrate([
            'cardLarge' => $dto->isCardLarge(),
            'imageCovered' => $dto->isImageCovered(),
            'imageScaled' => $dto->isImageScaled(),
            'imageScale' => $dto->getImageScale(),
            'imageOffsetted' => $dto->isImageOffsetted(),
            'imageOffsetX' => $dto->getImageOffsetX(),
            'imageOffsetY' => $dto->getImageOffsetY(),
        ]);

        $settings = $model->getPageSettings();
        $settings->setForIndex($indexSettings);

        $model->page_settings = $settings->toArray();

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to save');
        }
    }

    /**
     * @throws AdminServiceException
     */
    public function updateDetailPageSettings(int $id, DetailPageSettingsData $dto): void
    {
        $detailSettings = new DetailPagePageSettings();

        $detailSettings->setImageOffsetX($dto->getImageOffsetX());
        $detailSettings->setImageOffsetY($dto->getImageOffsetY());
        $detailSettings->setImageMaxHeight($dto->getImageMaxHeight());

        $model = $this->findOneById($id);

        $model->page_settings = $model->getPageSettings()
            ->setForDetail($detailSettings)
            ->toArray();

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to save');
        }
    }
}

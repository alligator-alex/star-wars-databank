<?php

declare(strict_types=1);

namespace App\Modules\Databank\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Common\Contracts\MediaData;
use App\Modules\Databank\Common\Models\Media;
use App\Modules\Databank\Common\Repositories\MediaRepository;

/**
 * @extends BaseService<Media>
 */
class MediaService extends BaseService
{
    public function __construct(MediaRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function relations(): array
    {
        return [
            'poster',
        ];
    }

    /**
     * Create new model.
     *
     * @param MediaData $dto
     *
     * @return Media
     *
     * @throws AdminServiceException
     */
    public function create(MediaData $dto): Media
    {
        /** @var Media $model */
        $model = $this->repository->getNewModel();

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update existing model.
     *
     * @param int $id
     * @param MediaData $dto
     *
     * @return Media
     *
     * @throws AdminServiceException
     */
    public function update(int $id, MediaData $dto): Media
    {
        $model = $this->find($id);

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update model data with DTO and save.
     *
     * @param Media $model
     * @param MediaData $dto
     *
     * @return Media
     *
     * @throws AdminServiceException
     */
    private function updateWithDto(Media $model, MediaData $dto): Media
    {
        $model->name = $dto->getName();
        $model->slug = $dto->getSlug();
        $model->status = $dto->getStatus();
        $model->sort = $dto->getSort() ?? 500;
        $model->type = $dto->getType();
        $model->release_date = $dto->getReleaseDate();
        $model->poster_id = $dto->getPosterId();

        if (!$this->repository->save($model)) {
            throw new AdminServiceException('Unable to save');
        }

        return $model;
    }
}

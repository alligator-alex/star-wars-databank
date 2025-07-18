<?php

declare(strict_types=1);

namespace App\Modules\Media\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Admin\Services\BaseService;
use App\Modules\Databank\Common\Repositories\BaseRepository;
use App\Modules\Media\Common\Contracts\MediaData;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Media\Public\Enums\CacheKeyPrefix;
use Illuminate\Support\Facades\Cache;

/**
 * @extends BaseService<Media>
 */
class MediaService extends BaseService
{
    /** @var MediaRepository */
    protected BaseRepository $repository;

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
        $model = $this->repository->newModel();

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
        $model = $this->findOneById($id);

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

        $this->forgetCache();

        return $model;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function dropdownList(): array
    {
        return $this->repository->dropdownList(true);
    }

    private function forgetCache(): void
    {
        Cache::forget(CacheKeyPrefix::ALL->value);
        Cache::forget(CacheKeyPrefix::AVAILABLE_TYPES->value);
        Cache::forget(CacheKeyPrefix::DROPDOWN_LIST->value);
    }
}

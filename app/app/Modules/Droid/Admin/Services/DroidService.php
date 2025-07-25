<?php

declare(strict_types=1);

namespace App\Modules\Droid\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Core\Common\Helpers\CacheHelper;
use App\Modules\Databank\Admin\Services\BaseService;
use App\Modules\Databank\Admin\Traits\ServiceWithPageSettings;
use App\Modules\Databank\Public\Enums\CacheKeyPrefix as DatabankCacheKeyPrefix;
use App\Modules\Droid\Common\Contracts\DroidData;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Droid\Public\Enums\CacheKeyPrefix;
use Illuminate\Support\Facades\Cache;

/**
 * @extends BaseService<Droid>
 */
class DroidService extends BaseService
{
    use ServiceWithPageSettings;

    public function __construct(DroidRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function relations(): array
    {
        return [
            'image',
            'line',
            'model',
            'class',
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
    public function create(DroidData $dto): Droid
    {
        /** @var Droid $model */
        $model = $this->repository->newModel();

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update existing model.
     *
     * @throws AdminServiceException
     */
    public function update(int $id, DroidData $dto): Droid
    {
        $model = $this->findOneById($id);

        return $this->updateWithDto($model, $dto);
    }

    /**
     * Update model data with DTO and save.
     *
     * @throws AdminServiceException
     */
    private function updateWithDto(Droid $model, DroidData $dto): Droid
    {
        $model->name = $dto->getName();
        $model->slug = $dto->getSlug();
        $model->status = $dto->getStatus();
        $model->sort = $dto->getSort() ?? 500;
        $model->external_url = $dto->getExternalUrl();

        $model->line_id = $dto->getLineId();
        $model->model_id = $dto->getModelId();
        $model->class_id = $dto->getClassId();
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

        $this->forgetCache($model);

        return $model;
    }

    public function delete(int $id): Droid
    {
        $model = parent::delete($id);

        $this->forgetCache($model);

        return $model;
    }

    public function togglePublish(int $id): Droid
    {
        $model = parent::togglePublish($id);

        $this->forgetCache($model);

        return $model;
    }

    private function forgetCache(Droid $model): void
    {
        Cache::forget(CacheHelper::makeKey(CacheKeyPrefix::ONE, $model->slug));
        Cache::forget(CacheHelper::makeKey(CacheKeyPrefix::ONE, $model->slug, 'draft'));

        Cache::forget(CacheKeyPrefix::PAGINATED->value);
        CacheHelper::forgetByWildcard(CacheKeyPrefix::PAGINATED);
        CacheHelper::forgetByWildcard(CacheKeyPrefix::RANDOM);

        CacheHelper::forgetByWildcard(DatabankCacheKeyPrefix::EXPLORE);
    }
}

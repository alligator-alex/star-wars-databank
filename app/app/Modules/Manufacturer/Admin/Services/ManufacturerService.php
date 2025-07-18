<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Admin\Services;

use App\Modules\Core\Admin\Exceptions\AdminServiceException;
use App\Modules\Databank\Admin\Services\BaseService;
use App\Modules\Databank\Common\Repositories\BaseRepository;
use App\Modules\Manufacturer\Common\Contracts\ManufacturerData;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Manufacturer\Common\Repositories\ManufacturerRepository;
use App\Modules\Manufacturer\Public\Enums\CacheKeyPrefix;
use Illuminate\Support\Facades\Cache;

/**
 * @extends BaseService<Manufacturer>
 */
class ManufacturerService extends BaseService
{
    /** @var ManufacturerRepository */
    protected BaseRepository $repository;

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
        $model = $this->repository->newModel();

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
        $model = $this->findOneById($id);

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

        $this->forgetCache();

        return $model;
    }

    /**
     * @return array<string, string>
     */
    public function dropdownList(): array
    {
        return $this->repository->dropdownList(true);
    }

    private function forgetCache(): void
    {
        Cache::forget(CacheKeyPrefix::DROPDOWN_LIST->value);
    }
}

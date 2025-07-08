<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\DTOs;

use App\Modules\Core\Common\Components\ValueObject;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Vehicle\Common\Contracts\VehicleData;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\CategorySpecificTechSpecs;
use App\Modules\Vehicle\Common\Helpers\VehicleHelper;

class Vehicle extends ValueObject implements VehicleData
{
    private string $name;
    private ?string $slug;
    private Status $status;
    private ?int $sort;
    private ?int $lineId = null;
    private ?int $categoryId = null;
    private ?int $typeId = null;
    private ?int $imageId = null;
    private ?string $description = null;
    private ?string $externalUrl = null;

    /** @var int[] */
    private array $manufacturersIds = [];

    /** @var int[] */
    private array $factionsIds = [];
    private ?int $mainFactionId = null;

    /** @var int[] */
    private array $appearancesIds = [];

    private ?CategorySpecificTechSpecs $techSpecs = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): void
    {
        $this->sort = $sort;
    }

    public function getLineId(): ?int
    {
        return $this->lineId;
    }

    public function setLineId(?int $lineId): void
    {
        $this->lineId = $lineId;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function setTypeId(?int $typeId): void
    {
        $this->typeId = $typeId;
    }

    public function getImageId(): ?int
    {
        return $this->imageId;
    }

    public function setImageId(?int $imageId): void
    {
        $this->imageId = $imageId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(?string $externalUrl): void
    {
        $this->externalUrl = $externalUrl;
    }

    /**
     * @return int[]
     */
    public function getManufacturersIds(): array
    {
        return $this->manufacturersIds;
    }

    /**
     * @param int[] $manufacturersIds
     */
    public function setManufacturersIds(array $manufacturersIds): void
    {
        $this->manufacturersIds = $manufacturersIds;
    }

    public function addManufacturerId(int $manufacturerId): void
    {
        if (in_array($manufacturerId, $this->manufacturersIds, true)) {
            return;
        }

        $this->manufacturersIds[] = $manufacturerId;
    }

    public function getFactionsIds(): array
    {
        return $this->factionsIds;
    }

    /**
     * @param int[] $factionsIds
     */
    public function setFactionsIds(array $factionsIds): void
    {
        $this->factionsIds = $factionsIds;
    }

    public function addFactionId(int $factionId): void
    {
        if (in_array($factionId, $this->factionsIds, true)) {
            return;
        }

        $this->factionsIds[] = $factionId;
    }

    public function getMainFactionId(): ?int
    {
        return $this->mainFactionId;
    }

    public function setMainFactionId(?int $mainFactionId): void
    {
        $this->mainFactionId = $mainFactionId;
    }

    public function getAppearancesIds(): array
    {
        return $this->appearancesIds;
    }

    /**
     * @param int[] $appearancesIds
     */
    public function setAppearancesIds(array $appearancesIds): void
    {
        $this->appearancesIds = $appearancesIds;
    }

    public function addAppearanceId(int $appearanceId): void
    {
        if (in_array($appearanceId, $this->appearancesIds, true)) {
            return;
        }

        $this->appearancesIds[] = $appearanceId;
    }

    public function getTechSpecs(): ?CategorySpecificTechSpecs
    {
        return $this->techSpecs;
    }

    public function setTechSpecs(?CategorySpecificTechSpecs $techSpecs): void
    {
        $this->techSpecs = $techSpecs;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function hydrate(array $data): static
    {
        $result = new static();

        $result->setName((string) $data['name']);
        $result->setCategoryId(isset($data['categoryId']) ? (int) $data['categoryId'] : null);
        $result->setTypeId(isset($data['typeId']) ? (int) $data['typeId'] : null);
        $result->setLineId(isset($data['lineId']) ? (int) $data['lineId'] : null);
        $result->setImageId(isset($data['imageId']) ? (int) $data['imageId'] : null);
        $result->setDescription((string) $data['description'] ?: null);
        $result->setExternalUrl((string) $data['externalUrl'] ?: null);

        $result->setManufacturersIds((array) $data['manufacturersIds']);

        $result->setFactionsIds((array) $data['factionsIds']);
        $result->setMainFactionId((int) $data['mainFactionId']);

        $result->setAppearancesIds((array) $data['appearancesIds']);

        if (!empty($data['technicalSpecifications']) && $result->getCategoryId()) {
            /** @var HandbookValue $category */
            $category = self::handbookValueRepository()->findOneById($result->getCategoryId());

            $result->setTechSpecs(VehicleHelper::hydrateTechSpecs($category, (array) $data['technicalSpecifications']));
        }

        return $result;
    }

    private static function handbookValueRepository(): HandbookValueRepository
    {
        static $handbookValueRepository = null;

        if ($handbookValueRepository === null) {
            /** @var HandbookValueRepository $handbookValueRepository */
            $handbookValueRepository = app()->make(HandbookValueRepository::class);
        }

        return $handbookValueRepository;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'categoryId' => $this->getCategoryId(),
            'typeId' => $this->getTypeId(),
            'lineId' => $this->getLineId(),
            'imageId' => $this->getImageId(),
            'description' => $this->getDescription(),
            'externalUrl' => $this->getExternalUrl(),
            'manufacturersIds' => $this->getManufacturersIds(),
            'factionsIds' => $this->getFactionsIds(),
            'mainFactionId' => $this->getMainFactionId(),
            'appearancesIds' => $this->getAppearancesIds(),
            'technicalSpecifications' => $this->getTechSpecs(),
        ];
    }
}

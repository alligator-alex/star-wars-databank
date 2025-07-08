<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\Services;

use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Helpers\DescriptionHelper;
use App\Modules\Databank\Import\DTOs\Appearance;
use App\Modules\Databank\Import\DTOs\Vehicle as VehicleDTO;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Manufacturer\Common\Repositories\ManufacturerRepository;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\CategorySpecificTechSpecs;
use App\Modules\Vehicle\Common\Helpers\VehicleHelper;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class VehicleService extends EntityService
{
    /** @var array<string, HandbookValue> */
    private array $categoriesCache = [];

    /** @var array<string, HandbookValue> */
    private array $typesCache = [];

    /** @var array<string, HandbookValue> */
    private array $linesCache = [];

    public function __construct(
        protected LoggerInterface $logger,
        protected FactionRepository $factionRepository,
        protected ManufacturerRepository $manufacturerRepository,
        protected MediaRepository $mediaRepository,
        protected HandbookRepository $handbookRepository,
        protected HandbookValueRepository $handbookValueRepository,
        private readonly VehicleRepository $vehicleRepository,
    ) {
        parent::__construct(
            $this->logger,
            $this->factionRepository,
            $this->manufacturerRepository,
            $this->mediaRepository,
            $this->handbookRepository,
            $this->handbookValueRepository
        );
    }

    /**
     * @param VehicleDTO $dto
     * @param bool $skipExisting
     *
     * @return void
     *
     * @throws Throwable
     */
    public function import(VehicleDTO $dto, bool $skipExisting = false): void
    {
        if (!$dto->isCanon() && $dto->getRelatedUrl()) {
            $vehicle = $this->findVehicleByExternalUrl($dto->getRelatedUrl());
        } else {
            $vehicle = $this->findVehicleByExternalUrl($dto->getExternalUrl());
        }

        if (!$vehicle) {
            $vehicle = $this->findVehicleByName($dto->getName());
        }

        if ($vehicle && $skipExisting) {
            $this->logger->notice('Skipped: existing Vehicle #' . $vehicle->id . ' "' . $vehicle->name . '"'
                . ' (' . route(VehicleRouteName::DETAIL, $vehicle->slug) . ')');
            return;
        }

        if ($vehicle?->canon && !$dto->isCanon()) {
            $this->saveVehicleAppearancesOnly($vehicle, $dto->getAppearances());
            $this->logger->info('Updated existing canon Vehicle #' . $vehicle->id . ' "' . $vehicle->name . '"'
                . ' (' . route(VehicleRouteName::DETAIL, $vehicle->slug) . ') with non-canon appearances');
            return;
        }

        if (!$vehicle) {
            $vehicle = $this->vehicleRepository->newModel();

            $vehicle->status = Status::DRAFT;
        }

        $vehicle = $this->saveVehicle($vehicle, $dto);

        $logPrefix = $vehicle->wasChanged()
            ? 'Updated existing'
            : 'Added new';

        $this->logger->info($logPrefix . ' Vehicle: #' . $vehicle->id . ' "' . $vehicle->name . '"'
            . ' (' . route(VehicleRouteName::DETAIL, $vehicle->slug) . ')');
    }

    private function findVehicleByExternalUrl(string $externalUrl): ?Vehicle
    {
        return $this->vehicleRepository->findByExternalUrl($externalUrl, true);
    }

    private function findVehicleByName(string $name): ?Vehicle
    {
        return $this->vehicleRepository->findOneByName($name, true);
    }

    private function findOrCreateCategory(string $name): HandbookValue
    {
        $slug = Str::slug($name);
        if (isset($this->categoriesCache[$slug])) {
            return $this->categoriesCache[$slug];
        }

        static $categoriesHandbook = null;
        if ($categoriesHandbook === null) {
            /** @var Handbook $categoriesHandbook */
            $categoriesHandbook = $this->handbookRepository->findOneByType(HandbookType::VEHICLE_CATEGORY);
        }

        $category = $this->handbookValueRepository->findOneBySlug($categoriesHandbook->id, $slug);
        if ($category) {
            return $this->categoriesCache[$slug] = $category;
        }

        $category = $this->handbookValueRepository->newModel();

        $category->handbook_id = $categoriesHandbook->id;
        $category->name = Str::title($name);
        $category->slug = $slug;

        if (!$this->handbookValueRepository->save($category)) {
            throw new RuntimeException('Unable to save Vehicle category');
        }

        $this->logger->info('Added new Vehicle category: #' . $category->id . ' "' . $category->name . '"');

        return $this->categoriesCache[$slug] = $category;
    }

    private function findOrCreateType(string $name): HandbookValue
    {
        $slug = Str::slug($name);
        if (isset($this->typesCache[$slug])) {
            return $this->typesCache[$slug];
        }

        static $typesHandbook = null;
        if ($typesHandbook === null) {
            /** @var Handbook $typesHandbook */
            $typesHandbook = $this->handbookRepository->findOneByType(HandbookType::VEHICLE_TYPE);
        }

        $type = $this->handbookValueRepository->findOneBySlug($typesHandbook->id, $slug);
        if ($type) {
            return $this->typesCache[$slug] = $type;
        }

        $type = $this->handbookValueRepository->newModel();

        $type->handbook_id = $typesHandbook->id;
        $type->name = $name;
        $type->slug = $slug;

        if (!$this->handbookValueRepository->save($type)) {
            throw new RuntimeException('Unable to save Vehicle type');
        }

        $this->logger->info('Added new Vehicle type: #' . $type->id . ' "' . $type->name . '"');

        return $this->typesCache[$slug] = $type;
    }

    private function findOrCreateLine(string $name): HandbookValue
    {
        $slug = Str::slug($name);
        if (isset($this->linesCache[$slug])) {
            return $this->linesCache[$slug];
        }

        static $linesHandbook = null;
        if ($linesHandbook === null) {
            /** @var Handbook $linesHandbook */
            $linesHandbook = $this->handbookRepository->findOneByType(HandbookType::VEHICLE_LINE);
        }

        $line = $this->handbookValueRepository->findOneBySlug($linesHandbook->id, $slug);
        if ($line) {
            return $this->linesCache[$slug] = $line;
        }

        $line = $this->handbookValueRepository->newModel();

        $line->handbook_id = $linesHandbook->id;
        $line->name = $name;
        $line->slug = $slug;

        if (!$this->handbookValueRepository->save($line)) {
            throw new RuntimeException('Unable to save Vehicle line');
        }

        $this->logger->info('Added new Vehicle line: #' . $line->id . ' "' . $line->name . '"');

        return $this->linesCache[$slug] = $line;
    }

    /**
     * @param HandbookValue $category
     * @param array<int, array<string, string|null>> $techSpecsData
     *
     * @return CategorySpecificTechSpecs
     */
    private function getCategorySpecificTechSpecsDTO(
        HandbookValue $category,
        array $techSpecsData
    ): CategorySpecificTechSpecs {
        $dto = VehicleHelper::resolveTechSpecs($category);
        if (!$dto) {
            throw new RuntimeException('Unable to resolve Vehicle specifications class');
        }

        foreach ($techSpecsData as $techSpec) {
            $value = trim((string)$techSpec['value']) ?: null;
            if (!$value) {
                continue;
            }

            $method = match ($techSpec['name']) {
                'Length' => 'setLength',
                'Width' => 'setWidth',
                'Height' => 'setHeight',
                'Diameter' => 'setDiameter',
                'Maximum acceleration' => 'setMaxAcceleration',
                'Maximum speed' => 'setMaxSpeed',
                'Maximum atmospheric speed' => 'setMaxSpeed',
                'MGLT' => 'setMglt',
                'Hyperdrive rating' => 'setHyperdriveRating',
                default => null,
            };

            if ($method !== null && method_exists($dto, $method)) {
                $dto->{$method}($value);
            }
        }

        return $dto;
    }

    /**
     * @param Vehicle $vehicle
     * @param Appearance[] $appearances
     *
     * @return void
     *
     * @throws Throwable
     */
    private function saveVehicleAppearancesOnly(Vehicle $vehicle, array $appearances): void
    {
        try {
            DB::beginTransaction();

            $mediaIds = [];
            foreach ($appearances as $appearance) {
                $media = $this->getOrCreateMedia($appearance);
                if (in_array($media->id, $mediaIds, true)) {
                    continue;
                }

                $mediaIds[] = $media->id;
            }

            if (!empty($mediaIds)) {
                $vehicle->appearances()->syncWithoutDetaching($mediaIds);
            }

            DB::commit();

            return;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Vehicle $vehicle
     * @param VehicleDTO $dto
     *
     * @return Vehicle
     *
     * @throws Throwable
     */
    private function saveVehicle(Vehicle $vehicle, VehicleDTO $dto): Vehicle
    {
        try {
            DB::beginTransaction();

            $vehicle->name = $dto->getName();
            $vehicle->external_url = $dto->getExternalUrl();
            $vehicle->canon = $dto->isCanon();

            $category = null;
            if ($dto->getCategoryName()) {
                $category = $this->findOrCreateCategory($dto->getCategoryName());
                $vehicle->category_id = $category->id;
            }

            if ($dto->getTypeName()) {
                $vehicle->type_id = $this->findOrCreateType($dto->getTypeName())->id;
            }

            if ($dto->getLineName()) {
                $vehicle->line_id = $this->findOrCreateLine($dto->getLineName())->id;
            }

            if ($dto->getImageUrl()) {
                // delete non-canon image if exists
                if ($vehicle->exists && $vehicle->image_id && !$vehicle->canon && $dto->isCanon()) {
                    $vehicle->image->delete();
                }

                try {
                    $attachment = $this->getOrCreateAttachment(
                        $dto->getName(),
                        $dto->getImageUrl(),
                        AttachmentGroup::VEHICLE_IMAGE
                    );

                    $vehicle->image_id = $attachment->id;

                    unset($attachment);
                } catch (Throwable $e) {
                    $this->logger->error(
                        'Unable to save image: ' . $e->getMessage() . ' (see: ' . $dto->getExternalUrl() . ')'
                    );
                }
            }

            $description = null;
            if ($dto->getDescription()) {
                $descriptionParts = explode(PHP_EOL, $dto->getDescription());

                $description = '<p>' . $descriptionParts[0] . '</p>';

                if (count($descriptionParts) > 1) {
                    $description .= PHP_EOL . '<p>' . $descriptionParts[1] . '</p>';
                }
            }

            if ($description) {
                $vehicle->description = DescriptionHelper::beautify($description, $vehicle->name);
            }

            if ($category && !empty($dto->getTechSpecs())) {
                $vehicle->technical_specifications = $this->getCategorySpecificTechSpecsDTO(
                    $category,
                    $dto->getTechSpecs()
                )->toArray();
            }

            if (!$vehicle->image_id || !$vehicle->description) {
                $vehicle->status = Status::DRAFT;
                $this->logger->warning('No image or description, keeping as draft');
            } else {
                $vehicle->status = Status::PUBLISHED;
            }

            if (!$this->vehicleRepository->save($vehicle)) {
                throw new RuntimeException('Unable to save Vehicle model');
            }

            $manufacturersIds = [];
            foreach ($dto->getManufacturersNames() as $manufacturerName) {
                $manufacturer = $this->getOrCreateManufacturer($manufacturerName);
                if (in_array($manufacturer->id, $manufacturersIds, true)) {
                    continue;
                }

                $manufacturersIds[] = $manufacturer->id;
            }

            if (!empty($manufacturersIds)) {
                $vehicle->manufacturers()->sync($manufacturersIds);
            }

            unset($manufacturersIds);

            $factionsIds = [];
            foreach ($dto->getFactionsNames() as $factionName) {
                $faction = $this->getOrCreateFaction($factionName);
                if (in_array($faction->id, $factionsIds, true)) {
                    continue;
                }

                $factionsIds[] = $faction->id;
            }

            if (!empty($factionsIds)) {
                $vehicle->factions()->sync($factionsIds);
            }

            unset($factionsIds);

            if ($dto->getMainFactionName()) {
                $faction = $this->getOrCreateFaction($dto->getMainFactionName());

                $vehicle->factions()->updateExistingPivot($faction->id, [
                    'main' => true,
                ]);

                unset($faction);
            }

            $mediaIds = [];
            foreach ($dto->getAppearances() as $appearance) {
                $media = $this->getOrCreateMedia($appearance);
                if (in_array($media->id, $mediaIds, true)) {
                    continue;
                }

                $mediaIds[] = $media->id;
            }

            if (!empty($mediaIds)) {
                $vehicle->appearances()->syncWithoutDetaching($mediaIds);
            }

            unset($mediaIds);

            DB::commit();

            return $vehicle;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

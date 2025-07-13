<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\Services;

use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Helpers\DescriptionHelper;
use App\Modules\Databank\Import\DTOs\Appearance;
use App\Modules\Databank\Import\DTOs\Droid as DroidDTO;
use App\Modules\Droid\Common\DTOs\TechSpecs;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Manufacturer\Common\Repositories\ManufacturerRepository;
use App\Modules\Media\Common\Repositories\MediaRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class DroidService extends EntityService
{
    /** @var array<string, HandbookValue> */
    private array $linesCache = [];

    /** @var array<string, HandbookValue> */
    private array $modelsCache = [];

    /** @var array<string, HandbookValue> */
    private array $classesCache = [];

    public function __construct(
        protected LoggerInterface $logger,
        protected FactionRepository $factionRepository,
        protected ManufacturerRepository $manufacturerRepository,
        protected MediaRepository $mediaRepository,
        protected HandbookRepository $handbookRepository,
        protected HandbookValueRepository $handbookValueRepository,
        private readonly DroidRepository $droidRepository,
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
     * @param DroidDTO $dto
     * @param bool $skipExisting
     *
     * @return void
     *
     * @throws Throwable
     */
    public function import(DroidDTO $dto, bool $skipExisting = false): void
    {
        if (!$dto->isCanon() && $dto->getRelatedUrl()) {
            $droid = $this->findDroidByExternalUrl($dto->getRelatedUrl());
        } else {
            $droid = $this->findDroidByExternalUrl($dto->getExternalUrl());
        }

        if (!$droid) {
            $droid = $this->findDroidByName($dto->getName());
        }

        if ($droid && $skipExisting) {
            $this->logger->notice('Skipped: existing Droid #' . $droid->id . ' "' . $droid->name . '"'
                . ' (' . route(DroidRouteName::DETAIL, $droid->slug) . ')');
            return;
        }

        if ($droid?->canon && !$dto->isCanon()) {
            $this->saveDroidAppearancesOnly($droid, $dto->getAppearances());
            $this->logger->info('Updated existing canon Droid #' . $droid->id . ' "' . $droid->name . '"'
                . ' (' . route(DroidRouteName::DETAIL, $droid->slug) . ') with non-canon appearances');
            return;
        }

        if (!$droid) {
            $droid = $this->droidRepository->newModel();

            $droid->status = Status::DRAFT;
        }

        $droid = $this->saveDroid($droid, $dto);

        $logPrefix = $droid->wasChanged()
            ? 'Updated existing'
            : 'Added new';

        $this->logger->info($logPrefix . ' Droid: #' . $droid->id . ' "' . $droid->name . '"'
            . ' (' . route(DroidRouteName::DETAIL, $droid->slug) . ')');
    }

    private function findDroidByExternalUrl(string $externalUrl): ?Droid
    {
        return $this->droidRepository->findByExternalUrl($externalUrl, true);
    }

    private function findDroidByName(string $name): ?Droid
    {
        return $this->droidRepository->findOneByName($name, true);
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
            $linesHandbook = $this->handbookRepository->findOneByType(HandbookType::DROID_LINE);
        }

        $line = $this->handbookValueRepository->findOneBySlug($linesHandbook->id, $slug);
        if ($line) {
            return $this->linesCache[$slug] = $line;
        }

        $line = $this->handbookValueRepository->newModel();

        $line->handbook_id = $linesHandbook->id;
        $line->name = Str::title($name);
        $line->slug = $slug;

        if (!$this->handbookValueRepository->save($line)) {
            throw new RuntimeException('Unable to save Droid line');
        }

        $this->logger->info('Added new Droid line: #' . $line->id . ' "' . $line->name . '"');

        return $this->linesCache[$slug] = $line;
    }

    private function findOrCreateModel(string $name): HandbookValue
    {
        $slug = Str::slug($name);
        if (isset($this->modelsCache[$slug])) {
            return $this->modelsCache[$slug];
        }

        static $modelsHandbook = null;
        if ($modelsHandbook === null) {
            /** @var Handbook $modelsHandbook */
            $modelsHandbook = $this->handbookRepository->findOneByType(HandbookType::DROID_MODEL);
        }

        $model = $this->handbookValueRepository->findOneBySlug($modelsHandbook->id, $slug);
        if ($model) {
            return $this->modelsCache[$slug] = $model;
        }

        $model = $this->handbookValueRepository->newModel();

        $model->handbook_id = $modelsHandbook->id;
        $model->name = Str::title($name);
        $model->slug = $slug;

        if (!$this->handbookValueRepository->save($model)) {
            throw new RuntimeException('Unable to save Droid model');
        }

        $this->logger->info('Added new Droid model: #' . $model->id . ' "' . $model->name . '"');

        return $this->modelsCache[$slug] = $model;
    }

    private function findOrCreateClass(string $name): HandbookValue
    {
        $slug = Str::slug($name);
        if (isset($this->classesCache[$slug])) {
            return $this->classesCache[$slug];
        }

        static $classesHandbook = null;
        if ($classesHandbook === null) {
            /** @var Handbook $classesHandbook */
            $classesHandbook = $this->handbookRepository->findOneByType(HandbookType::DROID_CLASS);
        }

        $class = $this->handbookValueRepository->findOneBySlug($classesHandbook->id, $slug);
        if ($class) {
            return $this->classesCache[$slug] = $class;
        }

        $class = $this->handbookValueRepository->newModel();

        $class->handbook_id = $classesHandbook->id;
        $class->name = Str::title($name);
        $class->slug = $slug;

        if (!$this->handbookValueRepository->save($class)) {
            throw new RuntimeException('Unable to save Droid class');
        }

        $this->logger->info('Added new Droid class: #' . $class->id . ' "' . $class->name . '"');

        return $this->classesCache[$slug] = $class;
    }

    /**
     * @param array<int, array<string, string|null>> $techSpecsData
     *
     * @return TechSpecs
     */
    private function getTechSpecsDTO(array $techSpecsData): TechSpecs
    {
        $dto = new TechSpecs();

        foreach ($techSpecsData as $techSpec) {
            $value = trim((string) $techSpec['value']) ?: null;
            if (!$value) {
                continue;
            }

            $method = match ($techSpec['name']) {
                'Height' => 'setHeight',
                'Mass' => 'setMass',
                'Gender' => 'setGender',
                default => null,
            };

            if ($method !== null && method_exists($dto, $method)) {
                $dto->{$method}($value);
            }
        }

        return $dto;
    }

    /**
     * @param Droid $droid
     * @param Appearance[] $appearances
     *
     * @return void
     *
     * @throws Throwable
     */
    private function saveDroidAppearancesOnly(Droid $droid, array $appearances): void
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
                $droid->appearances()->syncWithoutDetaching($mediaIds);
            }

            DB::commit();

            return;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Droid $droid
     * @param DroidDTO $dto
     *
     * @return Droid
     *
     * @throws Throwable
     */
    private function saveDroid(Droid $droid, DroidDTO $dto): Droid
    {
        try {
            DB::beginTransaction();

            $droid->name = $dto->getName();
            $droid->external_url = $dto->getExternalUrl();
            $droid->canon = $dto->isCanon();

            if ($dto->getLineName()) {
                $droid->line_id = $this->findOrCreateLine($dto->getLineName())->id;
            }

            if ($dto->getModelName()) {
                $droid->model_id = $this->findOrCreateModel($dto->getModelName())->id;
            }

            if ($dto->getClassName()) {
                $droid->class_id = $this->findOrCreateClass($dto->getClassName())->id;
            }

            if ($dto->getImageUrl()) {
                // delete non-canon image if exists
                if ($droid->exists && $droid->image_id && !$droid->canon && $dto->isCanon()) {
                    $droid->image->delete();
                }

                try {
                    $attachment = $this->getOrCreateAttachment(
                        $dto->getName(),
                        $dto->getImageUrl(),
                        AttachmentGroup::DROID_IMAGE
                    );

                    $droid->image_id = $attachment->id;

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
                $droid->description = $description;
                DescriptionHelper::beautify($droid);
            }

            if (!empty($dto->getTechSpecs())) {
                $droid->technical_specifications = $this->getTechSpecsDTO($dto->getTechSpecs())->toArray();
            }

            if (!$droid->image_id || !$droid->description) {
                $droid->status = Status::DRAFT;
                $this->logger->warning('No image or description, keeping as draft');
            } else {
                $droid->status = Status::PUBLISHED;
            }

            if (!$this->droidRepository->save($droid)) {
                throw new RuntimeException('Unable to save Droid model');
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
                $droid->manufacturers()->sync($manufacturersIds);
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
                $droid->factions()->sync($factionsIds);
            }

            unset($factionsIds);

            if ($dto->getMainFactionName()) {
                $faction = $this->getOrCreateFaction($dto->getMainFactionName());

                $droid->factions()->updateExistingPivot($faction->id, [
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
                $droid->appearances()->syncWithoutDetaching($mediaIds);
            }

            unset($mediaIds);

            DB::commit();

            return $droid;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

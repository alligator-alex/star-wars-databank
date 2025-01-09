<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\Importer;

use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Databank\Common\DTOs\CategorySpecificTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\AirTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\AquaticTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\GroundTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\RepulsorliftTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\SpaceStationTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\StarshipTechSpecs;
use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Common\Enums\VehicleType;
use App\Modules\Databank\Common\Enums\MediaType;
use App\Modules\Databank\Common\Helpers\VehicleHelper;
use App\Modules\Databank\Common\Models\Faction;
use App\Modules\Databank\Common\Models\Line;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Models\Media;
use App\Modules\Databank\Common\Repositories\FactionRepository;
use App\Modules\Databank\Common\Repositories\LineRepository;
use App\Modules\Databank\Common\Repositories\ManufacturerRepository;
use App\Modules\Databank\Common\Repositories\VehicleRepository;
use App\Modules\Databank\Common\Repositories\MediaRepository;
use App\Modules\Databank\Import\Contracts\Importer;
use App\Modules\Databank\Import\DTOs\Appearance;
use App\Modules\Databank\Import\DTOs\Vehicle as VehicleDTO;
use App\Modules\Databank\Import\Exceptions\EmptyImageUrlException;
use App\Modules\Databank\Import\Exceptions\GetImageContentException;
use App\Modules\Databank\Import\Exceptions\NoMajorAppearanceException;
use App\Modules\Databank\Import\Exceptions\NoPhysicalImageException;
use App\Modules\Databank\Import\Exceptions\TempImageRealPathException;
use App\Modules\Databank\Import\Exceptions\TempImageWriteException;
use App\Modules\Databank\Import\Exceptions\UnknownImageMimeTypeException;
use App\Modules\Databank\Public\Enums\VehicleRouteName;
use Carbon\Exceptions\InvalidFormatException;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use Orchid\Attachment\Engines\Generator as AttachmentGenerator;
use Orchid\Attachment\File;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class WookieepediaImporter implements Importer
{
    /** @var array<string, string> */
    private array $unknownTypes = [];

    /** @var array<string, Line> */
    private array $linesCache = [];

    /** @var array<string, Manufacturer> */
    private array $manufacturersCache = [];

    /** @var array<string, Faction> */
    private array $factionsCache = [];

    /** @var array<string, Media> */
    private array $mediaCache = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly VehicleRepository $vehicleRepository,
        private readonly LineRepository $lineRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly FactionRepository $factionRepository,
        private readonly MediaRepository $mediaRepository
    ) {
    }

    /**
     * @param iterable<VehicleDTO> $items
     * @param bool $skipExisting
     *
     * @return void
     *
     * @throws NoMajorAppearanceException
     * @throws Throwable
     */
    public function import(iterable $items, bool $skipExisting = false): void
    {
        foreach ($items as $dto) {
            $vehicle = null;

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
                    . ' (' . route(VehicleRouteName::ONE, $vehicle->slug) . ')');
                continue;
            }

            if ($vehicle?->canon && !$dto->isCanon()) {
                $this->saveVehicleAppearancesOnly($vehicle, $dto->getAppearances());
                $this->logger->info('Updated existing canon Vehicle #' . $vehicle->id . ' "' . $vehicle->name . '"'
                    . ' (' . route(VehicleRouteName::ONE, $vehicle->slug) . ') with non-canon appearances');
                continue;
            }

            if (!$vehicle) {
                $vehicle = $this->vehicleRepository->getNewModel();

                $vehicle->status = Status::DRAFT;
            }

            $vehicle = $this->saveVehicle($vehicle, $dto);

            $logPrefix = $vehicle->wasChanged()
                ? 'Updated existing'
                : 'Added new';

            $this->logger->info($logPrefix . ' Vehicle: #' . $vehicle->id . ' "' . $vehicle->name . '"'
                . ' (' . route(VehicleRouteName::ONE, $vehicle->slug) . ')');
        }
    }

    private function findVehicleByExternalUrl(string $externalUrl): ?Vehicle
    {
        return $this->vehicleRepository->findByExternalUrl($externalUrl, true);
    }

    private function findVehicleByName(string $name): ?Vehicle
    {
        return $this->vehicleRepository->findByName($name, true);
    }

    private function resolveVehicleCategory(string $category): ?VehicleCategory
    {
        return match (mb_strtolower($category)) {
            'air' => VehicleCategory::AIR,
            'aquatic' => VehicleCategory::AQUATIC,
            'ground' => VehicleCategory::GROUND,
            'repulsorlift' => VehicleCategory::REPULSORLIFT,
            'space station' => VehicleCategory::SPACE_STATION,
            'starship' => VehicleCategory::STARSHIP,
            default => null,
        };
    }

    private function resolveVehicleType(string $typeName): ?VehicleType
    {
        if (isset($this->unknownTypes[$typeName])) {
            return null;
        }

        // why so many of them? wtf...
        return match (mb_strtolower($typeName)) {
            'gunship',
            'droid lander'
            => VehicleType::GUNSHIP,

            'airbus',
            'airspeeder',
            'airspeeder/speeder',
            'assault airspeeder',
            'assault speeder',
            'atmospheric repulsorcraft',
            'atmospheric vehicle',
            'dropship assault transport/gunboat/speeder',
            'hovertrain',
            'mini-rig',
            'ski speeder',
            'speeder bus',
            'swoop',
            'fire suppression ship'
            => VehicleType::AIRSPEEDER,

            'atmospheric fighter'
            => VehicleType::ATMOSPHERIC_FIGHTER,

            'battlecruiser',
            'battlecruiser-classed star destroyer',
            'battleship',
            'droid control ship'
            => VehicleType::BATTLECRUISER,

            'space battle station',
            'deep-space mobile battlestation',
            'battlestation'
            => VehicleType::BATTLE_STATION,

            'heavy bomber',
            'light bomber',
            'space/planetary bomber'
            => VehicleType::BOMBER,

            'corvette'
            => VehicleType::CORVETTE,

            'destroyer',
            'light carrier',
            'light destroyer',
            'star destroyer'
            => VehicleType::DESTROYER,

            'arquitens-class command cruiser',
            'arrestor cruiser',
            'assault ship',
            'battlesphere',
            'core ship',
            'cruiser',
            'cruiser/light carrier',
            'heavy star cruiser',
            'heavy cruiser',
            'lander',
            'light cruiser',
            'mc-series star cruiser',
            'space cruiser',
            'star cruiser',
            'jamming ship'
            => VehicleType::CRUISER,

            'box freighter',
            'bulk freighter',
            'cargo freighter',
            'cargo frigate',
            'freighter',
            'light freighter',
            'freight transport'
            => VehicleType::FREIGHTER,

            'frigate',
            'star frigate',
            'corsair'
            => VehicleType::FRIGATE,

            'heavy patrol speeder',
            'landspeeder (truckspeeder)',
            'landspeeder',
            'lightweight repulsorlift vehicle',
            'limospeeder',
            'luxury sail barge',
            'repulsorcraft cargo skiff',
            'repulsorcraft troop transport',
            'repulsorcraft',
            'repulsorlift skiff',
            'repulsorlift vehicle',
            'repulsorlift',
            'sail barge',
            'skimmer',
            'speeder bike',
            'speeder',
            'swoop bike',
            'ground vehicle'
            => VehicleType::LANDSPEEDER,

            'podracer'
            => VehicleType::PODRACER,

            'armed government transport shuttle',
            'assault shuttle',
            'cargo shuttle',
            'orbital shuttle',
            'personal transport',
            'planetary shuttle',
            'priority personnel shuttle',
            'shuttle',
            'shuttle/transport',
            'transport shuttle',
            'commuter ship'
            => VehicleType::SHUTTLE,

            'tug',
            'spacetug'
            => VehicleType::TUG,

            'aerospace superiority starfighter',
            'assault starfighter',
            'assault starfighter/bomber',
            'attack starfighter',
            'droid starfighter',
            'heavy assault starfighter',
            'heavy assault starfighter/bombers',
            'light starfighter',
            'luxury starfighter',
            'space superiority starfighter',
            'starfighter',
            'starfighter-bomber',
            'starfighter/gunship',
            'starfighter/patrol craft',
            'lancer-class pursuit craft',
            'scout ship',
            'pursuit vessel',
            'patrol craft'
            => VehicleType::STARFIGHTER,

            'droid tank',
            'ground assault vehicle',
            'repulsorcraft tank',
            'repulsorlift tank vehicle',
            'repulsortank',
            'tank',
            'wheeled tank',
            'wheeled'
            => VehicleType::TANK,

            'train'
            => VehicleType::TRAIN,

            'armed transport',
            'carrier',
            'combat transport',
            'courier',
            'heavy transport starship',
            'hoversled',
            'hyperdrive pod',
            'medium transport',
            'multi-purpose transport',
            'multi-troop transport',
            'personal wheel bike',
            'repulsorlift gunship',
            'repulsorlift transport',
            'sandcrawler',
            'sled',
            'starliner',
            'supply ship',
            'transport',
            'treadable',
            'treaded ground transport',
            'treaded transport',
            'troop carrier',
            'troop transport',
            'wheeled land-based vehicle',
            'wheeled/walker',
            'landing craft',
            'prison ship',
            'mobile refinery',
            'dropship'
            => VehicleType::TRANSPORT,

            'armed cargo walker',
            'artillery combat droid',
            'assault walker',
            'base defense walker',
            'combat walker',
            'droid walker',
            'pod walker',
            'tug walker',
            'walker',
            'heavy artillery'
            => VehicleType::WALKER,

            'diplomatic barge',
            'luxury yacht',
            'skiff',
            'star yacht',
            'transport (diplomatic barge)',
            'yacht'
            => VehicleType::YACHT,

            'space station',
            'escape pod',
            'starship',
            'dockyard',
            'static defense platform',
            'unknown',
            'self-propelled artillery cannon',
            'lake barge',
            'boat',
            'sea skiff',
            'submarine'
            => VehicleType::OTHER,

            'star dreadnought',
            'siege dreadnought'
            => VehicleType::DREADNOUGHT,

            default => null,
        };
    }

    private function resolveMediaType(string $type): ?MediaType
    {
        return match (mb_strtolower($type)) {
            'movie' => MediaType::MOVIE,
            'series' => MediaType::SERIES,
            'game' => MediaType::GAME,
            default => null,
        };
    }

    private function getOrCreateMedia(Appearance $dto): Media
    {
        if (isset($this->mediaCache[$dto->getName()])) {
            return $this->mediaCache[$dto->getName()];
        }

        $media = $this->mediaRepository->findByName($dto->getName(), true);
        if ($media) {
            $this->mediaCache[$dto->getName()] = $media;
            return $media;
        }

        $media = $this->mediaRepository->getNewModel();

        $media->name = $dto->getName();
        $media->status = Status::PUBLISHED;
        $media->type = $dto->getTypeName()
            ? $this->resolveMediaType($dto->getTypeName())
            : null;

        try {
            $media->release_date = $dto->getReleaseDate()
                ? Carbon::createFromFormat('F j, Y', $dto->getReleaseDate())
                : null;
        } catch (InvalidFormatException $e) {
            $this->logger->error('Unable to create Carbon instance for date "' . $dto->getReleaseDate() . '":'
                . $e->getMessage());
        }

        if ($dto->getImageUrl()) {
            try {
                $media->poster_id = $this->getOrCreateAttachment(
                    $dto->getName(),
                    $dto->getImageUrl(),
                    AttachmentGroup::MEDIA_POSTER
                )->id;
            } catch (Throwable $e) {
                $this->logger->error('Unable to save image: ' . $e->getMessage()
                    . ' (see: ' . $dto->getImageUrl() . ')');
            }
        }

        if (!$this->mediaRepository->save($media)) {
            throw new RuntimeException('Unable to save Media model');
        }

        $this->logger->info('Added new Media: #' . $media->id . ' "' . $media->name . '"');

        $this->mediaCache[$dto->getName()] = $media;

        return $media;
    }

    private function getOrCreateLine(string $name): Line
    {
        if (isset($this->linesCache[$name])) {
            return $this->linesCache[$name];
        }

        $line = $this->lineRepository->findByName($name, true);
        if ($line) {
            $this->linesCache[$name] = $line;
            return $line;
        }

        $line = $this->lineRepository->getNewModel();

        $line->name = $name;
        $line->status = Status::PUBLISHED;

        if (!$this->lineRepository->save($line)) {
            throw new RuntimeException('Unable to save Line model');
        }

        $this->logger->info('Added new Line: #' . $line->id . ' "' . $line->name . '"');

        $this->linesCache[$name] = $line;

        return $line;
    }

    private function getOrCreateManufacturer(string $name): Manufacturer
    {
        if (isset($this->manufacturersCache[$name])) {
            return $this->manufacturersCache[$name];
        }

        $manufacturer = $this->manufacturerRepository->findByName($name, true);
        if ($manufacturer) {
            $this->manufacturersCache[$name] = $manufacturer;
            return $manufacturer;
        }

        $manufacturer = $this->manufacturerRepository->getNewModel();

        $manufacturer->name = $name;
        $manufacturer->status = Status::PUBLISHED;

        if (!$this->manufacturerRepository->save($manufacturer)) {
            throw new RuntimeException('Unable to save Manufacturer model');
        }

        $this->logger->info('Added new Manufacturer: #' . $manufacturer->id . ' "' . $manufacturer->name . '"');

        $this->manufacturersCache[$name] = $manufacturer;

        return $manufacturer;
    }

    private function getOrCreateFaction(string $name): Faction
    {
        if (isset($this->factionsCache[$name])) {
            return $this->factionsCache[$name];
        }

        $faction = $this->factionRepository->findByName($name, true);
        if ($faction) {
            $this->factionsCache[$name] = $faction;
            return $faction;
        }

        $faction = $this->factionRepository->getNewModel();

        $faction->name = $name;
        $faction->status = Status::PUBLISHED;

        if (!$this->factionRepository->save($faction)) {
            throw new RuntimeException('Unable to save Faction model');
        }

        $this->logger->info('Added new Faction: #' . $faction->id . ' "' . $faction->name . '"');

        $this->factionsCache[$name] = $faction;

        return $faction;
    }

    /**
     * @throws EmptyImageUrlException
     * @throws FilesystemException
     * @throws NoPhysicalImageException
     * @throws TempImageRealPathException
     * @throws TempImageWriteException
     * @throws UnknownImageMimeTypeException
     * @throws GetImageContentException
     */
    private function getOrCreateAttachment(string $name, string $url, AttachmentGroup $group): Attachment
    {
        if ($url === '') {
            throw new EmptyImageUrlException('Empty url');
        }

        $content = file_get_contents($url);
        if ($content === false) {
            throw new GetImageContentException('Invalid content');
        }

        $tempImage = tmpfile();
        if (!fwrite($tempImage, $content)) {
            fclose($tempImage);
            throw new TempImageWriteException('Unable to write image content to temporary file');
        }

        $mimeType = new finfo(FILEINFO_MIME_TYPE)->buffer($content);
        unset($content);

        $tempImagePath = stream_get_meta_data($tempImage)['uri'];
        if (!$tempImagePath) {
            fclose($tempImage);
            throw new TempImageRealPathException('Unable to get temporary image path');
        }

        $extension = match ($mimeType) {
            'image/png' => '.png',
            'image/jpeg' => '.jpg',
            'image/webp' => '.webp',
            default => null,
        };

        if (!$extension) {
            fclose($tempImage);
            throw new UnknownImageMimeTypeException('Unknown image mime type "' . $mimeType . '"');
        }

        $slug = Str::slug(title: $name, dictionary: ['/' => '-']);
        $uploadedFile = new UploadedFile($tempImagePath, $slug . $extension, $mimeType);

        $disk = config('platform.attachment.disk', 'public');
        if ($attachment = $this->findAttachmentByUploadedFile($uploadedFile, $disk)) {
            unlink($tempImagePath);
            return $attachment;
        }

        $file = new File($uploadedFile, $disk, $group->value);

        /** @var Attachment $attachment */
        $attachment = $file->load();

        unlink($tempImagePath);
        unset($file);

        if (!is_file(Storage::disk($attachment->disk)->path($attachment->physicalPath()))) {
            $attachment->delete();

            throw new NoPhysicalImageException('No physical image file at path'
                . ' "' . public_path($attachment->physicalPath()) . '"');
        }

        return $attachment;
    }

    /**
     * Try to find existing DB entry for image by its content hash.
     * This helps to prevent duplicate entries on subsequent runs.
     *
     * @param UploadedFile $file
     * @param string $disk
     *
     * @return Attachment|null
     *
     * @see File::__construct
     */
    private function findAttachmentByUploadedFile(UploadedFile $file, string $disk): ?Attachment
    {
        $generator = config('platform.attachment.generator', AttachmentGenerator::class);
        $engine = new $generator($file);

        return Attachment::where('hash', $engine->hash())
            ->where('disk', $disk)
            ->first();
    }

    /**
     * @param array<int, array<string, string|null>> $techSpecsData
     * @param VehicleCategory $category
     *
     * @return CategorySpecificTechSpecs
     */
    private function getCategorySpecificTechSpecsDTO(
        array $techSpecsData,
        VehicleCategory $category
    ): CategorySpecificTechSpecs {
        $dto = match ($category) {
            VehicleCategory::AIR => new AirTechSpecs(),
            VehicleCategory::AQUATIC => new AquaticTechSpecs(),
            VehicleCategory::GROUND => new GroundTechSpecs(),
            VehicleCategory::REPULSORLIFT => new RepulsorliftTechSpecs(),
            VehicleCategory::SPACE_STATION => new SpaceStationTechSpecs(),
            VehicleCategory::STARSHIP => new StarshipTechSpecs(),
        };

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

            if (!is_null($method) && method_exists($dto, $method)) {
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
            $vehicle->category = $dto->getCategoryName()
                ? $this->resolveVehicleCategory($dto->getCategoryName())
                : null;
            $vehicle->type = $dto->getTypeName()
                ? $this->resolveVehicleType($dto->getTypeName())
                : null;

            if (!$vehicle->type
                && $dto->getTypeName()
                && !isset($this->unknownTypes[$dto->getTypeName()])) {
                $this->unknownTypes[$dto->getTypeName()] = $dto->getTypeName();
                $this->logger->notice('New unknown type: "' . $dto->getTypeName() . '"'
                    . ' (see: ' . $dto->getExternalUrl() . ')');
            }

            if ($dto->getLineName()) {
                $line = $this->getOrCreateLine($dto->getLineName());
                $vehicle->line_id = $line->id;
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
                        'Unable to save image: ' . $e->getMessage()
                        . ' (see: ' . $dto->getExternalUrl() . ')'
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
                $vehicle->description = $description;
                VehicleHelper::beautifyDescription($vehicle);
            }

            if ($vehicle->category && !empty($dto->getTechSpecs())) {
                $vehicle->technical_specifications = $this->getCategorySpecificTechSpecsDTO(
                    $dto->getTechSpecs(),
                    $vehicle->category
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

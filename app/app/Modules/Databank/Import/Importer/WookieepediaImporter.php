<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\Importer;

use App\Modules\Databank\Import\Contracts\Importer;
use App\Modules\Databank\Import\DTOs\Droid as DroidDTO;
use App\Modules\Databank\Import\DTOs\Entity;
use App\Modules\Databank\Import\DTOs\Vehicle as VehicleDTO;
use App\Modules\Databank\Import\Services\DroidService;
use App\Modules\Databank\Import\Services\VehicleService;
use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Manufacturer\Common\Repositories\ManufacturerRepository;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class WookieepediaImporter implements Importer
{
    private VehicleService $vehicleService;
    private DroidService $droidService;

    public function __construct(
        private LoggerInterface $logger,
        private FactionRepository $factionRepository,
        private ManufacturerRepository $manufacturerRepository,
        private MediaRepository $mediaRepository,
        private HandbookRepository $handbookRepository,
        private HandbookValueRepository $handbookValueRepository
    ) {
        $this->vehicleService = new VehicleService(
            $this->logger,
            $this->factionRepository,
            $this->manufacturerRepository,
            $this->mediaRepository,
            $this->handbookRepository,
            $this->handbookValueRepository,
            new VehicleRepository()
        );

        $this->droidService = new DroidService(
            $this->logger,
            $this->factionRepository,
            $this->manufacturerRepository,
            $this->mediaRepository,
            $this->handbookRepository,
            $this->handbookValueRepository,
            new DroidRepository()
        );
    }

    /**
     * @param iterable<Entity> $items
     * @param bool $skipExisting
     *
     * @return void
     *
     * @throws Throwable
     */
    public function import(iterable $items, bool $skipExisting = false): void
    {
        foreach ($items as $dto) {
            match ($dto::class) {
                VehicleDTO::class => $this->importVehicle($dto, $skipExisting),
                DroidDTO::class => $this->importDroid($dto, $skipExisting),
            };
        }
    }

    /**
     * @throws Throwable
     */
    private function importVehicle(VehicleDTO $dto, bool $skipExisting = false): void
    {
        $this->logger->info('Importing Vehicle "' . $dto->getName() . '"...');

        $this->vehicleService->import($dto, $skipExisting);
    }

    /**
     * @throws Throwable
     */
    private function importDroid(DroidDTO $dto, bool $skipExisting = false): void
    {
        $this->logger->info('Importing Droid "' . $dto->getName() . '"...');

        $this->droidService->import($dto, $skipExisting);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\Parser;

use App\Modules\Databank\Import\Contracts\Parser;
use App\Modules\Databank\Import\DTOs\Appearance;
use App\Modules\Databank\Import\DTOs\Droid;
use App\Modules\Databank\Import\DTOs\Vehicle;
use App\Modules\Databank\Import\Enums\EntityType;
use App\Modules\Databank\Import\Exceptions\ParserException;
use App\Modules\Faction\Common\Helpers\FactionHelper;
use App\Modules\Manufacturer\Common\Helpers\ManufacturerHelper;
use App\Modules\Media\Common\Helpers\MediaHelper;
use App\Modules\Vehicle\Common\Helpers\VehicleLineHelper;
use App\Modules\Vehicle\Common\Helpers\VehicleTypeHelper;
use Illuminate\Support\Str;
use JsonException;
use Psr\Log\LoggerInterface;

class WookiepediaParser implements Parser
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * @param iterable<string> $items
     * @param EntityType|null $type
     *
     * @return iterable<Vehicle>
     *
     * @throws ParserException
     * @throws JsonException
     */
    public function parse(iterable $items, ?EntityType $type = null): iterable
    {
        if ($type) {
            $this->logger->info('Starting parsing ' . Str::title(Str::plural($type->value)) . '...');
        } else {
            $this->logger->info('Starting parsing...');
        }

        foreach ($items as $item) {
            $data = json_decode($item, true, 16, JSON_THROW_ON_ERROR);
            if ($data === null) {
                throw new ParserException('Unable to decode message JSON: ' . Str::limit($item, 5000));
            }

            $entityTypeName = mb_strtolower((string) $data['entityType']);

            $this->logger->info('Received ' . $entityTypeName . ' "' . $data['mainInfo']['name'] . '" (' . $data['mainInfo']['url'] . ')');

            $entityType = EntityType::tryFrom($entityTypeName);
            if (!$entityType) {
                throw new ParserException('Unknown entity type "' . $entityTypeName . '"');
            }

            if ($type && ($type !== $entityType)) {
                $this->logger->notice('Skipped: only "' . $type->value . '" expected');
                continue;
            }

            $dto = match ($entityType) {
                EntityType::VEHICLE => $this->getVehicleDto($data),
                EntityType::DROID => $this->getDroidDto($data),
            };

            if (empty($dto->getAppearances())) {
                $this->logger->notice('Skipped: no major appearance');
                continue;
            }

            yield $dto;
        }

        $this->logger->info('Parsing completed');
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Vehicle
     */
    private function getVehicleDto(array $data): Vehicle
    {
        $dto = new Vehicle((string) $data['mainInfo']['name'], (string) $data['mainInfo']['url']);

        $dto->setImageUrl(isset($data['mainInfo']['imageUrl']) ? (string) $data['mainInfo']['imageUrl'] : null);
        $dto->setDescription(isset($data['mainInfo']['description']) ? (string) $data['mainInfo']['description'] : null);
        $dto->setRelatedUrl(isset($data['mainInfo']['relatedUrl']) ? (string) $data['mainInfo']['relatedUrl'] : null);
        $dto->setCanon(isset($data['mainInfo']['isCanon']) && $data['mainInfo']['isCanon']);

        $dto->setCategoryName(isset($data['category']) ? (string) $data['category'] : null);
        $dto->setTypeName(isset($data['type']) ? VehicleTypeHelper::combineSimilar((string) $data['type']) : null);
        $dto->setLineName(isset($data['line']) ? VehicleLineHelper::combineSimilar((string) $data['line']) : null);

        $factions = $this->getMajorFactionsNames((array) ($data['factions'] ?? []));
        if (!empty($factions)) {
            $dto->setFactionsNames($factions);
            $dto->setMainFactionName($factions[0]);
        }

        $appearances = $this->getMajorAppearancesDTOs((array) ($data['appearances'] ?? []));
        if (!empty($appearances)) {
            $dto->setAppearances($appearances);
        }

        $manufacturers = $this->getManufacturersNames((array) ($data['manufacturers'] ?? []));
        if (!empty($manufacturers)) {
            $dto->setManufacturersNames($manufacturers);
        }

        $dto->setTechSpecs((array) ($data['technicalSpecifications'] ?? []));

        return $dto;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Droid
     */
    private function getDroidDto(array $data): Droid
    {
        $dto = new Droid((string) $data['mainInfo']['name'], (string) $data['mainInfo']['url']);

        $dto->setImageUrl(isset($data['mainInfo']['imageUrl']) ? (string) $data['mainInfo']['imageUrl'] : null);
        $dto->setDescription(isset($data['mainInfo']['description']) ? (string) $data['mainInfo']['description'] : null);
        $dto->setRelatedUrl(isset($data['mainInfo']['relatedUrl']) ? (string) $data['mainInfo']['relatedUrl'] : null);
        $dto->setCanon(isset($data['mainInfo']['isCanon']) && $data['mainInfo']['isCanon']);

        $dto->setLineName(isset($data['line']) ? (string) $data['line'] : null);
        $dto->setModelName(isset($data['model']) ? (string) $data['model'] : null);
        $dto->setClassName(isset($data['class']) ? (string) $data['class'] : null);

        $factions = $this->getMajorFactionsNames((array) ($data['factions'] ?? []));
        if (!empty($factions)) {
            $dto->setFactionsNames($factions);
            $dto->setMainFactionName($factions[0]);
        }

        $appearances = $this->getMajorAppearancesDTOs((array) ($data['appearances'] ?? []));
        if (!empty($appearances)) {
            $dto->setAppearances($appearances);
        }

        $manufacturers = $this->getManufacturersNames((array) ($data['manufacturers'] ?? []));
        if (!empty($manufacturers)) {
            $dto->setManufacturersNames($manufacturers);
        }

        $dto->setTechSpecs((array) ($data['technicalSpecifications'] ?? []));

        return $dto;
    }

    /**
     * @param array<int, array<string, mixed>> $data
     *
     * @return string[]
     */
    private function getManufacturersNames(array $data): array
    {
        $result = [];
        foreach ($data as $manufacturer) {
            if (!$manufacturer['name']) {
                continue;
            }

            $result[] = ManufacturerHelper::combineSimilar((string) $manufacturer['name']);
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $data
     *
     * @return string[]
     */
    private function getMajorFactionsNames(array $data): array
    {
        $result = [];
        foreach ($data as $faction) {
            if (!$faction['name']) {
                continue;
            }

            $note = mb_strtolower((string) $faction['note']);
            if (($note === 'stolen') || ($note === 'appropriated')) {
                continue;
            }

            if (!FactionHelper::isMajor((string) $faction['name'])) {
                continue;
            }

            $result[] = (string) $faction['name'];
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $data
     *
     * @return Appearance[]
     */
    private function getMajorAppearancesDTOs(array $data): array
    {
        $result = [];
        foreach ($data as $appearance) {
            if (!$appearance['name']) {
                continue;
            }

            if (!MediaHelper::isMajor((string) $appearance['name'])) {
                continue;
            }

            $dto = new Appearance((string) $appearance['name']);

            $dto->setImageUrl((string) $appearance['imageUrl']);
            $dto->setTypeName((string) $appearance['type']);
            $dto->setReleaseDate((string) $appearance['releaseDate']);

            $result[] = $dto;
        }

        return $result;
    }
}

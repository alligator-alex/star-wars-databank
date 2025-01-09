<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\Parser;

use App\Modules\Databank\Import\Contracts\Parser;
use App\Modules\Databank\Import\DTOs\Appearance;
use App\Modules\Databank\Import\DTOs\Vehicle;
use App\Modules\Databank\Import\Exceptions\ParserException;
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
     *
     * @return iterable<Vehicle>
     *
     * @throws ParserException
     * @throws JsonException
     */
    public function parse(iterable $items): iterable
    {
        $this->logger->info('Starting parsing...');

        foreach ($items as $item) {
            $data = json_decode($item, true, 16, JSON_THROW_ON_ERROR);
            if (is_null($data)) {
                throw new ParserException('Unable to decode message JSON: ' . Str::limit($item, 5000));
            }

            // TODO: droids?
            // TODO: weapons?
            $dto = $this->getVehicleDto($data);

            $this->logger->info('Parsing "' . $dto->getName() . '" (' . $dto->getExternalUrl() . ')');

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
        $dto = new Vehicle((string) $data['name'], (string) $data['url']);

        $dto->setCanon(isset($data['isCanon']) && $data['isCanon']);
        $dto->setRelatedUrl(isset($data['relatedUrl']) ? (string) $data['relatedUrl'] : null);
        $dto->setCategoryName(isset($data['category']) ? (string) $data['category'] : null);
        $dto->setTypeName(isset($data['type']) ? (string) $data['type'] : null);
        $dto->setLineName(isset($data['line']) ? $this->combineSimilarLines((string) $data['line']) : null);
        $dto->setImageUrl(isset($data['imageUrl']) ? (string) $data['imageUrl'] : null);
        $dto->setDescription(isset($data['description']) ? (string) $data['description'] : null);

        foreach ((array) ($data['manufacturers'] ?? []) as $manufacturerData) {
            if (!$manufacturerData['name']) {
                continue;
            }

            $dto->addManufacturerName($this->combineSimilarManufacturers((string) $manufacturerData['name']));
        }

        foreach ((array) ($data['factions'] ?? []) as $key => $factionData) {
            if (!$factionData['name']) {
                continue;
            }

            $note = mb_strtolower((string) $factionData['note']);
            if (($note === 'stolen') || ($note === 'appropriated')) {
                continue;
            }

            if (!$this->isMajorFaction((string) $factionData['name'])) {
                continue;
            }

            $dto->addFactionName((string) $factionData['name']);

            if ($key === 0) {
                $dto->setMainFactionName((string) $factionData['name']);
            }
        }

        if (!$dto->getMainFactionName() && !empty($dto->getFactionsNames())) {
            $dto->setMainFactionName($dto->getFactionsNames()[0]);
        }

        foreach ((array) ($data['appearances'] ?? []) as $appearanceData) {
            if (!$appearanceData['name']) {
                continue;
            }

            if (!$this->isMajorAppearance((string) $appearanceData['name'])) {
                continue;
            }

            $appearanceDto = new Appearance((string) $appearanceData['name']);

            $appearanceDto->setImageUrl((string) $appearanceData['imageUrl']);
            $appearanceDto->setTypeName((string) $appearanceData['type']);
            $appearanceDto->setReleaseDate((string) $appearanceData['releaseDate']);

            $dto->addAppearance($appearanceDto);
        }

        $dto->setTechSpecs((array) ($data['technicalSpecifications'] ?? []));

        return $dto;
    }

    /**
     * Combine same lines with different names.
     *
     * @param string $name
     *
     * @return string
     */
    private function combineSimilarLines(string $name): string
    {
        return match (mb_strtolower($name)) {
            'acclamator-class assault ship' => 'Acclamator-class',
            'all terrain', 'all terrain armored transport' => 'All-terrain vehicle',
            'btl y-wing starfighter', 'btl y-wing', 'y-wing starfighter' => 'Y-wing',
            'delta fighter' => 'Delta-series',
            'j-type star skiff' => 'J-type',
            'laat' => 'Low Altitude Assault Transport',
            'lucrehulk-class' => 'Lucrehulk',
            'mc80 star cruisers' => 'MC star cruiser',
            'tie series', 'tie fighter' => 'TIE series',
            'x-wing starfighter' => 'X-wing',
            default => $name,
        };
    }

    /**
     * Combine same manufacturers with different names.
     *
     * @param string $name
     *
     * @return string
     */
    private function combineSimilarManufacturers(string $name): string
    {
        return match (mb_strtolower($name)) {
            'cygnus spaceworks' => 'Cygnus Space Workshops',
            'haor chall egineering' => 'Haor Chall Engineering Corporation',
            'hoersch-kessel drive, inc.' => 'Hoersch-Kessel Drive Inc.',
            default => $name,
        };
    }

    /**
     * There are many factions in the Galaxy...but only a few of them really matter.
     *
     * @param string $name
     *
     * @return bool
     */
    private function isMajorFaction(string $name): bool
    {
        $map = [
            'Alliance to Restore the Republic',
            'Confederacy of Independent Systems',
            'First Order',
            'Galactic Empire',
            'Galactic Republic',
            'Jedi Order',
            'New Republic',
            'Resistance',
            'Sith',
        ];

        $name = mb_strtolower($name);

        return array_any($map, static fn (string $faction) => mb_strtolower($faction) === $name);
    }

    /**
     * Follow the main films timeline (32 BBY - 35 ABY)
     * And we really don't want to import vehicles from every series, books or novels (or Acolyte).
     *
     * @param string $name
     *
     * @return bool
     */
    private function isMajorAppearance(string $name): bool
    {
        $map = [
            // Prequel trilogy
            'Star Wars: Episode I The Phantom Menace',
            'Star Wars: Episode II Attack of the Clones',
            'Star Wars: Episode III Revenge of the Sith',

            // Original trilogy
            'Star Wars: Episode IV A New Hope',
            'Star Wars: Episode V The Empire Strikes Back',
            'Star Wars: Episode VI Return of the Jedi',

            // Sequel trilogy
            'Star Wars: Episode VII The Force Awakens',
            'Star Wars: Episode VIII The Last Jedi',
            'Star Wars: Episode IX The Rise of Skywalker',

            // Spin-off films
            'Rogue One: A Star Wars Story',
            'Solo: A Star Wars Story',

            // Series
            'Ahsoka',
            'Andor',
            'Obi-Wan Kenobi',
            'The Book of Boba Fett',
            'The Mandalorian',

            // Games
            'Star Wars: Jedi Knight II: Jedi Outcast',
            'Star Wars: Jedi Knight: Jedi Academy',
            'Star Wars: Empire at War',
            'Star Wars: Republic Commando',
            'Star Wars: The Force Unleashed',
            'Star Wars: The Force Unleashed II',
            'Star Wars: Battlefront', // Pandemic Studios classic
            'Star Wars: Battlefront II',
            'Star Wars Battlefront', // DICE remake
            'Star Wars Battlefront II',
            'Star Wars: Squadrons',
            'Star Wars Jedi: Fallen Order',
            'Star Wars Jedi: Survivor',
            'Star Wars Outlaws',
        ];

        $name = mb_strtolower($name);

        return array_any($map, static fn (string $mediaName) => mb_strtolower($mediaName) === $name);
    }
}

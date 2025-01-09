<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Enums;

use App\Modules\Databank\Common\Contracts\HumanReadableEnum;

enum VehicleType: int implements HumanReadableEnum
{
    // Combat ships
    case STARFIGHTER = 1;
    case BOMBER = 2;
    case GUNSHIP = 3;

    // Capital ships (by Anaxes War College System)
    case CORVETTE = 4; // 100-200 meters
    case FRIGATE = 5; // 200-400 meters
    case CRUISER = 6; // 400-600 meters
    case HEAVY_CRUISER = 7; // 600-1200 meters
    case DESTROYER = 8; // 1000-2000 meters
    case BATTLECRUISER = 9; // 2000-5000 meters
    case DREADNOUGHT = 10; // 5000+ meters

    // Common
    case AIRSPEEDER = 11;
    case LANDSPEEDER = 12;
    case WALKER = 13;
    case SHUTTLE = 14;
    case TANK = 15;
    case TRANSPORT = 16;
    case FREIGHTER = 17;

    // Other
    case BATTLE_STATION = 18;
    case PODRACER = 19;
    case ATMOSPHERIC_FIGHTER = 20;
    case TRAIN = 21;
    case TUG = 22;
    case YACHT = 23;

    case OTHER = 32767;

    public function nameForHumans(): string
    {
        return match ($this) {
            self::STARFIGHTER => 'Starfighter',
            self::BOMBER => 'Bomber',
            self::GUNSHIP => 'Gunship',

            self::CORVETTE => 'Corvette',
            self::FRIGATE => 'Frigate',
            self::CRUISER => 'Cruiser',
            self::HEAVY_CRUISER => 'Heavy cruiser',
            self::DESTROYER => 'Destroyer',
            self::BATTLECRUISER => 'Battlecruiser',
            self::DREADNOUGHT => 'Dreadnought',

            self::AIRSPEEDER => 'Airspeeder',
            self::LANDSPEEDER => 'Landspeeder',
            self::WALKER => 'Walker',
            self::SHUTTLE => 'Shuttle',
            self::TANK => 'Tank',
            self::TRANSPORT => 'Transport',
            self::FREIGHTER => 'Freighter',

            self::BATTLE_STATION => 'Battle station',
            self::PODRACER => 'Podracer',
            self::ATMOSPHERIC_FIGHTER => 'Atmospheric fighter',
            self::TRAIN => 'Train',
            self::TUG => 'Tug',
            self::YACHT => 'Yacht',

            self::OTHER => 'Other',
        };
    }
}

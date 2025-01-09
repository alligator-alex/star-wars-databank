<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Enums;

enum VehicleCategory: int
{
    case AIR = 1;
    case AQUATIC = 2;
    case GROUND = 3;
    case REPULSORLIFT = 4;
    case SPACE_STATION = 5;
    case STARSHIP = 6;

    public function nameForHumans(): string
    {
        return match ($this) {
            self::AIR => 'Air',
            self::AQUATIC => 'Aquatic',
            self::GROUND => 'Ground',
            self::REPULSORLIFT => 'Repulsorlift',
            self::SPACE_STATION => 'Space Station',
            self::STARSHIP => 'Starship',
        };
    }
}

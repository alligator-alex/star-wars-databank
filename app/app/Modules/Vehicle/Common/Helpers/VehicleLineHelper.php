<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\Helpers;

class VehicleLineHelper
{
    public static function combineSimilar(string $name): string
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
}

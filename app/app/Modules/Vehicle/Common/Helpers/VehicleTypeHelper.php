<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\Helpers;

class VehicleTypeHelper
{
    public static function combineSimilar(string $name): string
    {
        return match (mb_strtolower($name)) {
            'gunship',
            'droid lander'
                => 'Gunship',

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
                => 'Airspeeder',

            'atmospheric fighter' => 'Atmospheric fighter',

            'battlecruiser',
            'battlecruiser-classed star destroyer',
            'battleship',
            'droid control ship'
                => 'Battlecruiser',

            'space battle station',
            'deep-space mobile battlestation',
            'battlestation'
                => 'Battle station',

            'heavy bomber',
            'light bomber',
            'space/planetary bomber'
                => 'Bomber',

            'corvette' => 'Corvette',

            'destroyer',
            'light carrier',
            'light destroyer',
            'star destroyer'
                => 'Destroyer',

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
                => 'Cruiser',

            'box freighter',
            'bulk freighter',
            'cargo freighter',
            'cargo frigate',
            'freighter',
            'light freighter',
            'freight transport'
                => 'Freighter',

            'frigate',
            'star frigate',
            'corsair'
                => 'Frigate',

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
                => 'Landspeeder',

            'podracer' => 'Podracer',

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
                => 'Shuttle',

            'tug',
            'spacetug'
                => 'Tug',

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
                => 'Starfighter',

            'droid tank',
            'ground assault vehicle',
            'repulsorcraft tank',
            'repulsorlift tank vehicle',
            'repulsortank',
            'tank',
            'wheeled tank',
            'wheeled'
                => 'Tank',

            'train' => 'Train',

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
                => 'Transport',

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
                => 'Walker',

            'diplomatic barge',
            'luxury yacht',
            'skiff',
            'star yacht',
            'transport (diplomatic barge)',
            'yacht'
                => 'Yacht',

            'star dreadnought',
            'siege dreadnought'
                => 'Dreadnought',

            default => 'Other',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\Helpers;

use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\AirTechSpecs;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\AquaticTechSpecs;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\CategorySpecificTechSpecs;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\GroundTechSpecs;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\RepulsorliftTechSpecs;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\SpaceStationTechSpecs;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\StarshipTechSpecs;

class VehicleHelper
{
    /**
     * @param HandbookValue $category
     *
     * @return CategorySpecificTechSpecs|null
     */
    public static function resolveTechSpecs(HandbookValue $category): ?CategorySpecificTechSpecs
    {
        return match ($category->slug) {
            'air' => new AirTechSpecs(),
            'aquatic' => new AquaticTechSpecs(),
            'ground' => new GroundTechSpecs(),
            'repulsorlift' => new RepulsorliftTechSpecs(),
            'space-station' => new SpaceStationTechSpecs(),
            'starship' => new StarshipTechSpecs(),
            default => null,
        };
    }

    /**
     * @param HandbookValue $category
     * @param array<string, mixed> $data
     *
     * @return CategorySpecificTechSpecs|null
     */
    public static function hydrateTechSpecs(HandbookValue $category, array $data): ?CategorySpecificTechSpecs
    {
        $dto = self::resolveTechSpecs($category);
        if (!$dto) {
            return null;
        }

        return $dto::hydrate($data);
    }
}

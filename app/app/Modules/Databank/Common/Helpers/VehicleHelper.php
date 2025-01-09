<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Helpers;

use App\Modules\Databank\Common\DTOs\CategorySpecificTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\AirTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\AquaticTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\GroundTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\RepulsorliftTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\SpaceStationTechSpecs;
use App\Modules\Databank\Common\DTOs\TechSpecs\StarshipTechSpecs;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Common\Models\Vehicle;

class VehicleHelper
{
    public static function beautifyDescription(Vehicle $model): Vehicle
    {
        $description = $model->description;

        $strongNamePosition = mb_strpos($description, '<strong>' . $model->name);
        if ($strongNamePosition === false) {
            $position = mb_strpos($description, $model->name);
            if ($position !== false) {
                $startString = mb_substr($description, 0, $position);
                $endString = mb_substr($description, $position + mb_strlen($model->name));

                $description = $startString . '<strong>' . $model->name . '</strong>' . $endString;
            }
        }

        $nonBreakingSpace = '&nbsp;';

        $description = str_replace([
            'a ',
            'an ',
            'as ',
            'by ',
            'for ',
            'in ',
            'of ',
            'the ',
            'to ',
        ], [
            'a' . $nonBreakingSpace,
            'an' . $nonBreakingSpace,
            'as' . $nonBreakingSpace,
            'by' . $nonBreakingSpace,
            'for' . $nonBreakingSpace,
            'in' . $nonBreakingSpace,
            'of' . $nonBreakingSpace,
            'the' . $nonBreakingSpace,
            'to' . $nonBreakingSpace,
        ], $description);

        $model->description = $description;

        return $model;
    }

    /**
     * @param VehicleCategory $category
     * @param array<string, mixed> $data
     *
     * @return CategorySpecificTechSpecs
     */
    public static function hydrateTechSpecs(VehicleCategory $category, array $data): CategorySpecificTechSpecs
    {
        return match ($category) {
            VehicleCategory::AIR => AirTechSpecs::hydrate($data),
            VehicleCategory::AQUATIC => AquaticTechSpecs::hydrate($data),
            VehicleCategory::GROUND => GroundTechSpecs::hydrate($data),
            VehicleCategory::REPULSORLIFT => RepulsorliftTechSpecs::hydrate($data),
            VehicleCategory::SPACE_STATION => SpaceStationTechSpecs::hydrate($data),
            VehicleCategory::STARSHIP => StarshipTechSpecs::hydrate($data),
        };
    }
}

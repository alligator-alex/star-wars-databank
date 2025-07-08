<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\Factories;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * @var class-string<Vehicle>
     */
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'slug' => $this->faker->slug,
            'status' => Status::PUBLISHED->value,
            'sort' => $this->faker->randomNumber(3),
            'external_url' => $this->faker->url,
            'category_id' => HandbookValue::factory()->ofHandbookType(HandbookType::VEHICLE_CATEGORY),
            'type_id' => HandbookValue::factory()->ofHandbookType(HandbookType::VEHICLE_TYPE),
            'line_id' => HandbookValue::factory()->ofHandbookType(HandbookType::VEHICLE_LINE),
            'image_id' => null,
            'description' => $this->faker->text(1000),
            'technical_specifications' => [],
            'page_settings' => [],
        ];
    }
}

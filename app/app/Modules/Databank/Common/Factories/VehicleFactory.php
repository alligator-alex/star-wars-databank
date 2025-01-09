<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Factories;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Common\Enums\VehicleType;
use App\Modules\Databank\Common\Models\Line;
use App\Modules\Databank\Common\Models\Vehicle;
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
            'category' => $this->faker->randomElement(VehicleCategory::cases())->value,
            'type' => $this->faker->randomElement(VehicleType::cases())->value,
            'line_id' => Line::factory(),
            'image_id' => null,
            'description' => $this->faker->text(1000),
            'technical_specifications' => [],
            'page_settings' => [],
        ];
    }
}

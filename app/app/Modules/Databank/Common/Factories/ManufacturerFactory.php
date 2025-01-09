<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Factories;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    /**
     * @var class-string<Manufacturer>
     */
    protected $model = Manufacturer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'slug' => $this->faker->slug,
            'status' => Status::PUBLISHED->value,
            'sort' => $this->faker->randomNumber(3),
        ];
    }
}

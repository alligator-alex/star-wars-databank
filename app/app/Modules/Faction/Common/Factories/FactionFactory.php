<?php

declare(strict_types=1);

namespace App\Modules\Faction\Common\Factories;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Faction\Common\Models\Faction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faction>
 */
class FactionFactory extends Factory
{
    /**
     * @var class-string<Faction>
     */
    protected $model = Faction::class;

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

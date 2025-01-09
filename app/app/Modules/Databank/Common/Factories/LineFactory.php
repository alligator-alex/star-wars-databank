<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Factories;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Models\Line;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Line>
 */
class LineFactory extends Factory
{
    /**
     * @var class-string<Line>
     */
    protected $model = Line::class;

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

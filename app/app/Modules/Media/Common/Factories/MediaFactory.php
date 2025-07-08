<?php

declare(strict_types=1);

namespace App\Modules\Media\Common\Factories;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Media\Common\Enums\MediaType;
use App\Modules\Media\Common\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * @var class-string<Media>
     */
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'slug' => $this->faker->slug,
            'status' => Status::PUBLISHED->value,
            'sort' => $this->faker->randomNumber(3),
            'type' => $this->faker->randomElement(MediaType::cases())->value,
            'release_date' => $this->faker->date,
            'poster_id' => null,
        ];
    }
}

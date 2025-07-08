<?php

declare(strict_types=1);

namespace App\Modules\Droid\Common\Factories;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Droid>
 */
class DroidFactory extends Factory
{
    /**
     * @var class-string<Droid>
     */
    protected $model = Droid::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'slug' => $this->faker->slug,
            'status' => Status::PUBLISHED->value,
            'sort' => $this->faker->randomNumber(3),
            'external_url' => $this->faker->url,
            'line_id' => HandbookValue::factory()->ofHandbookType(HandbookType::DROID_LINE),
            'model_id' => HandbookValue::factory()->ofHandbookType(HandbookType::DROID_MODEL),
            'class_id' => HandbookValue::factory()->ofHandbookType(HandbookType::DROID_CLASS),
            'image_id' => null,
            'description' => $this->faker->text(1000),
            'technical_specifications' => [],
            'page_settings' => [],
        ];
    }
}

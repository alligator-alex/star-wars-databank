<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Factories;

use App\Modules\Core\Common\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * @var class-string<Attachment>
     */
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'original_name' => $this->faker->name,
            'mime' => $this->faker->mimeType(),
            'extension' => $this->faker->fileExtension(),
            'size' => $this->faker->randomNumber(3),
            'sort' => $this->faker->randomNumber(3),
            'path' => $this->faker->filePath(),
            'description' => $this->faker->text,
            'alt' => $this->faker->text,
            'hash' => $this->faker->slug,
            'disk' => 'public',
            'group' => null,
        ];
    }
}

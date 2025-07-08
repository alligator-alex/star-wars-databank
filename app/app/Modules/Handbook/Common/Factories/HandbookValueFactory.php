<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Factories;

use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use Illuminate\Database\Eloquent\Factories\Factory;
use RuntimeException;

/**
 * @extends Factory<HandbookValue>
 */
class HandbookValueFactory extends Factory
{
    /**
     * @var class-string<HandbookValue>
     */
    protected $model = HandbookValue::class;

    /**
     * @inheritdoc
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->word . md5(microtime()),
        ];
    }

    public function ofHandbook(Handbook $handbook): HandbookValueFactory
    {
        return $this->state(function (array $attributes) use ($handbook) {
            return [
                'handbook_id' => $handbook->id,
            ];
        });
    }

    public function ofHandbookType(HandbookType $type): HandbookValueFactory
    {
        return $this->state(function (array $attributes) use ($type) {
            /** @var HandbookRepository $handbookRepository */
            $handbookRepository = app()->make(HandbookRepository::class);
            $handbook = $handbookRepository->findOneByType($type);

            if ($handbook === null) {
                throw new RuntimeException('Handbook "' . $type->name . '" not found');
            }

            return [
                'handbook_id' => $handbook->id,
            ];
        });
    }
}

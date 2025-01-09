<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Components;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

abstract class ValueObject implements Arrayable, JsonSerializable
{
    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    abstract public static function hydrate(array $data): static;

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

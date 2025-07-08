<?php

declare(strict_types=1);

namespace App\Modules\Droid\Common\DTOs;

use App\Modules\Core\Common\Components\ValueObject;

class TechSpecs extends ValueObject
{
    protected ?string $height = null;
    protected ?string $mass = null;
    protected ?string $gender = null;

    /**
     * @return array<string, string>
     */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->toArray() as $key => $value) {
            if ($value === null) {
                continue;
            }

            $items[$this->keysHumanReadable()[$key] ?? $key] = $value;
        }

        return $items;
    }

    /**
     * @return array<string, string>
     */
    protected function keysHumanReadable(): array
    {
        return [
            'height' => __('Height'),
            'mass' => __('Mass'),
            'gender' => __('Gender'),
        ];
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): void
    {
        $this->height = $height;
    }

    public function getMass(): ?string
    {
        return $this->mass;
    }

    public function setMass(?string $mass): void
    {
        $this->mass = $mass;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function hydrate(array $data): static
    {
        $result = new static();

        $result->setHeight($data['height'] ?? null);
        $result->setMass($data['mass'] ?? null);
        $result->setGender($data['gender'] ?? null);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'height' => $this->getHeight(),
            'mass' => $this->getMass(),
            'gender' => $this->getGender(),
        ];
    }
}

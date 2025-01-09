<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\DTOs;

use App\Modules\Core\Common\Components\ValueObject;

abstract class CategorySpecificTechSpecs extends ValueObject
{
    protected ?string $length = null;
    protected ?string $width = null;
    protected ?string $height = null;
    protected ?string $maxSpeed = null;

    /**
     * @return array<string, string>
     */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->toArray() as $key => $value) {
            if (is_null($value)) {
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
            'length' => __('Length'),
            'maxSpeed' => __('Max. speed'),
            'width' => __('Width'),
        ];
    }

    public function getLength(): ?string
    {
        return $this->length;
    }

    public function setLength(?string $length): void
    {
        $this->length = $length;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setWidth(?string $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): void
    {
        $this->height = $height;
    }

    public function getMaxSpeed(): ?string
    {
        return $this->maxSpeed;
    }

    public function setMaxSpeed(?string $maxSpeed): void
    {
        $this->maxSpeed = $maxSpeed;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function hydrate(array $data): static
    {
        $result = new static();

        $result->setLength($data['length'] ?? null);
        $result->setWidth($data['width'] ?? null);
        $result->setHeight($data['height'] ?? null);
        $result->setMaxSpeed($data['maxSpeed'] ?? null);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'length' => $this->getLength(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'maxSpeed' => $this->getMaxSpeed(),
        ];
    }
}

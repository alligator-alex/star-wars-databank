<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\DTOs\TechSpecs;

class SpaceStationTechSpecs extends CategorySpecificTechSpecs
{
    private ?string $diameter = null;
    private ?string $mglt = null;

    public function getDiameter(): ?string
    {
        return $this->diameter;
    }

    public function setDiameter(?string $diameter): void
    {
        $this->diameter = $diameter;
    }

    public function getMglt(): ?string
    {
        return $this->mglt;
    }

    public function setMglt(?string $mglt): void
    {
        $this->mglt = $mglt;
    }

    public static function hydrate(array $data): static
    {
        $result = parent::hydrate($data);

        $result->setDiameter($data['diameter'] ?? null);
        $result->setMglt($data['mglt'] ?? null);

        return $result;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'diameter' => $this->getDiameter(),
            'mglt' => $this->getMglt(),
        ]);
    }
}

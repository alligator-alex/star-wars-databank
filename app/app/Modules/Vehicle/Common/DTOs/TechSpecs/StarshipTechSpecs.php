<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\DTOs\TechSpecs;

class StarshipTechSpecs extends CategorySpecificTechSpecs
{
    private ?string $maxAcceleration = null;
    private ?string $mglt = null;
    private ?string $hyperdriveRating = null;

    public function getMaxAcceleration(): ?string
    {
        return $this->maxAcceleration;
    }

    public function setMaxAcceleration(?string $maxAcceleration): void
    {
        $this->maxAcceleration = $maxAcceleration;
    }

    public function getMglt(): ?string
    {
        return $this->mglt;
    }

    public function setMglt(?string $mglt): void
    {
        $this->mglt = $mglt;
    }

    public function getHyperdriveRating(): ?string
    {
        return $this->hyperdriveRating;
    }

    public function setHyperdriveRating(?string $hyperdriveRating): void
    {
        $this->hyperdriveRating = $hyperdriveRating;
    }

    public static function hydrate(array $data): static
    {
        $result = parent::hydrate($data);

        $result->setMaxAcceleration($data['maxAcceleration'] ?? null);
        $result->setMglt($data['mglt'] ?? null);
        $result->setHyperdriveRating($data['hyperdriveRating'] ?? null);

        return $result;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'maxAcceleration' => $this->getMaxAcceleration(),
            'mglt' => $this->getMglt(),
            'hyperdriveRating' => $this->getHyperdriveRating(),
        ]);
    }

    protected function keysHumanReadable(): array
    {
        return array_merge(parent::keysHumanReadable(), [
            'hyperdriveRating' => __('Hyperdrive rating'),
            'maxAcceleration' => __('Max. acceleration'),
            'maxSpeed' => __('Max. atmospheric speed'),
            'mglt' => __('MGLT'),
        ]);
    }
}

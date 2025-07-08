<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\DTOs;

class Droid extends Entity
{
    private ?string $lineName = null;
    private ?string $modelName = null;
    private ?string $className = null;

    /** @var string[] */
    private array $manufacturersNames = [];

    /** @var array<int, array<string, string|null>> */
    private array $techSpecs = [];

    public function getLineName(): ?string
    {
        return $this->lineName;
    }

    public function setLineName(?string $lineName): void
    {
        $this->lineName = $lineName;
    }

    public function getModelName(): ?string
    {
        return $this->modelName;
    }

    public function setModelName(?string $modelName): void
    {
        $this->modelName = $modelName;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(?string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string[]
     */
    public function getManufacturersNames(): array
    {
        return $this->manufacturersNames;
    }

    /**
     * @param string[] $manufacturersNames
     *
     * @return void
     */
    public function setManufacturersNames(array $manufacturersNames): void
    {
        $this->manufacturersNames = $manufacturersNames;
    }

    public function addManufacturerName(string $manufacturerName): void
    {
        $this->manufacturersNames[] = $manufacturerName;
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    public function getTechSpecs(): array
    {
        return $this->techSpecs;
    }

    /**
     * @param array<int, array<string, string|null>> $techSpecs
     *
     * @return void
     */
    public function setTechSpecs(array $techSpecs): void
    {
        $this->techSpecs = $techSpecs;
    }
}

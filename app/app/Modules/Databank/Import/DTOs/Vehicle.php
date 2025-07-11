<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\DTOs;

class Vehicle extends Entity
{
    private ?string $categoryName = null;
    private ?string $typeName = null;
    private ?string $lineName = null;

    /** @var string[] */
    private array $manufacturersNames = [];

    /** @var array<int, array<string, string|null>> */
    private array $techSpecs = [];

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function setCategoryName(?string $categoryName): void
    {
        $this->categoryName = $categoryName;
    }

    public function getTypeName(): ?string
    {
        return $this->typeName;
    }

    public function setTypeName(?string $typeName): void
    {
        $this->typeName = $typeName;
    }

    public function getLineName(): ?string
    {
        return $this->lineName;
    }

    public function setLineName(?string $lineName): void
    {
        $this->lineName = $lineName;
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

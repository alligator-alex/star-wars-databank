<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\DTOs;

class Vehicle
{
    private bool $canon = false;
    private ?string $relatedUrl = null;
    private ?string $categoryName = null;
    private ?string $typeName = null;
    private ?string $lineName = null;
    private ?string $imageUrl = null;
    private ?string $description = null;

    /** @var string[] */
    private array $manufacturersNames = [];

    /** @var string[] */
    private array $factionsNames = [];

    private ?string $mainFactionName = null;

    /** @var Appearance[] */
    private array $appearances = [];

    /** @var array<int, array<string, string|null>> */
    private array $techSpecs = [];

    public function __construct(private readonly string $name, private readonly string $externalUrl)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function isCanon(): bool
    {
        return $this->canon;
    }

    public function setCanon(bool $canon): void
    {
        $this->canon = $canon;
    }

    public function getRelatedUrl(): ?string
    {
        return $this->relatedUrl;
    }

    public function setRelatedUrl(?string $relatedUrl): void
    {
        $this->relatedUrl = $relatedUrl;
    }

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

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
     * @return string[]
     */
    public function getFactionsNames(): array
    {
        return $this->factionsNames;
    }

    /**
     * @param string[] $factionsNames
     *
     * @return void
     */
    public function setFactionsName(array $factionsNames): void
    {
        $this->factionsNames = $factionsNames;
    }

    public function addFactionName(string $factionName): void
    {
        $this->factionsNames[] = $factionName;
    }

    public function getMainFactionName(): ?string
    {
        return $this->mainFactionName;
    }

    public function setMainFactionName(?string $mainFactionName): void
    {
        $this->mainFactionName = $mainFactionName;
    }

    /**
     * @return Appearance[]
     */
    public function getAppearances(): array
    {
        return $this->appearances;
    }

    /**
     * @param Appearance[] $appearances
     *
     * @return void
     */
    public function setAppearances(array $appearances): void
    {
        $this->appearances = $appearances;
    }

    public function addAppearance(Appearance $appearance): void
    {
        $this->appearances[] = $appearance;
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

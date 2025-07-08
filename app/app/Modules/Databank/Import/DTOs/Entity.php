<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\DTOs;

abstract class Entity
{
    protected bool $canon = false;
    protected ?string $relatedUrl = null;
    protected ?string $imageUrl = null;
    protected ?string $description = null;

    /** @var string[] */
    protected array $factionsNames = [];

    protected ?string $mainFactionName = null;

    /** @var Appearance[] */
    protected array $appearances = [];

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
    public function getFactionsNames(): array
    {
        return $this->factionsNames;
    }

    /**
     * @param string[] $factionsNames
     *
     * @return void
     */
    public function setFactionsNames(array $factionsNames): void
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
}

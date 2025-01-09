<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\DTOs;

use App\Modules\Core\Common\Components\ValueObject;
use App\Modules\Databank\Common\Contracts\ManufacturerData;
use App\Modules\Databank\Common\Enums\Status;

class Manufacturer extends ValueObject implements ManufacturerData
{
    private string $name;
    private ?string $slug = null;
    private Status $status;
    private ?int $sort = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): void
    {
        $this->sort = $sort;
    }

    public static function hydrate(array $data): static
    {
        $result = new static();

        $result->setName((string) $data['name']);
        $result->setSlug($data['slug'] ? (string) $data['slug'] : null);
        $result->setStatus(Status::tryFrom((int) $data['status']));
        $result->setSort($data['sort'] ? (int) $data['sort'] : null);

        return $result;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'slug' => $this->getSlug(),
            'status' => $this->getStatus(),
            'sort' => $this->getSort(),
        ];
    }
}

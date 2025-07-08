<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\DTOs;

use App\Modules\Core\Common\Components\ValueObject;
use App\Modules\Databank\Common\DTOs\PageSettings\DetailPagePageSettings;
use App\Modules\Databank\Common\DTOs\PageSettings\IndexPageSettings;

class PageSettings extends ValueObject
{
    private IndexPageSettings $forIndex;
    private DetailPagePageSettings $forDetail;

    public function getForIndex(): IndexPageSettings
    {
        return $this->forIndex;
    }

    public function setForIndex(IndexPageSettings $forIndex): static
    {
        $this->forIndex = $forIndex;

        return $this;
    }

    public function getForDetail(): DetailPagePageSettings
    {
        return $this->forDetail;
    }

    public function setForDetail(DetailPagePageSettings $forDetail): static
    {
        $this->forDetail = $forDetail;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function hydrate(array $data): static
    {
        $result = new static();

        $result->setForIndex(IndexPageSettings::hydrate($data['index'] ?? $data['list'] ?? [])); // keep "list" for legacy data
        $result->setForDetail(DetailPagePageSettings::hydrate($data['detail'] ?? []));

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'index' => $this->getForIndex()->toArray(),
            'detail' => $this->getForDetail()->toArray(),
        ];
    }
}

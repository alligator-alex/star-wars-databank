<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\DTOs;

use App\Modules\Core\Common\Components\ValueObject;
use App\Modules\Databank\Common\DTOs\VehiclePageSettings\DetailPagePageSettings;
use App\Modules\Databank\Common\DTOs\VehiclePageSettings\ListPageSettings;

class VehiclePageSettings extends ValueObject
{
    private ListPageSettings $forList;
    private DetailPagePageSettings $forDetail;

    public function getForList(): ListPageSettings
    {
        return $this->forList;
    }

    public function setForList(ListPageSettings $forList): static
    {
        $this->forList = $forList;

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

        $result->setForList(ListPageSettings::hydrate($data['list'] ?? []));
        $result->setForDetail(DetailPagePageSettings::hydrate($data['detail'] ?? []));

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'list' => $this->getForList()->toArray(),
            'detail' => $this->getForDetail()->toArray(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\DTOs\VehiclePageSettings;

use App\Modules\Core\Common\Components\ValueObject;
use App\Modules\Databank\Common\Contracts\VehicleDetailPageSettingsData;

class DetailPagePageSettings extends ValueObject implements VehicleDetailPageSettingsData
{
    public const int IMAGE_OFFSET_X_DEFAULT = 48;
    public const int IMAGE_OFFSET_Y_DEFAULT = 10;
    public const int IMAGE_MAX_HEIGHT_DEFAULT = 90;

    private int $imageOffsetX = self::IMAGE_OFFSET_X_DEFAULT;
    private int $imageOffsetY = self::IMAGE_OFFSET_Y_DEFAULT;
    private int $imageMaxHeight = self::IMAGE_MAX_HEIGHT_DEFAULT;

    public function getImageOffsetX(): int
    {
        return $this->imageOffsetX;
    }

    public function setImageOffsetX(int $imageOffsetX): void
    {
        $this->imageOffsetX = $imageOffsetX;
    }

    public function getImageOffsetY(): int
    {
        return $this->imageOffsetY;
    }

    public function setImageOffsetY(int $imageOffsetY): void
    {
        $this->imageOffsetY = $imageOffsetY;
    }

    public function getImageMaxHeight(): int
    {
        return $this->imageMaxHeight;
    }

    public function setImageMaxHeight(int $imageMaxHeight): void
    {
        $this->imageMaxHeight = $imageMaxHeight;
    }

    public static function hydrate(array $data): static
    {
        $result = new static();

        if (isset($data['imageOffsetX'])) {
            $result->setImageOffsetX((int) $data['imageOffsetX']);
        }

        if (isset($data['imageOffsetY'])) {
            $result->setImageOffsetY((int) $data['imageOffsetY']);
        }

        if (isset($data['imageMaxHeight'])) {
            $result->setImageMaxHeight((int) $data['imageMaxHeight']);
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            'imageOffsetX' => $this->getImageOffsetX(),
            'imageOffsetY' => $this->getImageOffsetY(),
            'imageMaxHeight' => $this->getImageMaxHeight(),
        ];
    }
}

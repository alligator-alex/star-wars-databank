<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\DTOs\VehiclePageSettings;

use App\Modules\Core\Common\Components\ValueObject;
use App\Modules\Databank\Common\Contracts\VehicleListPageSettingsData;

class ListPageSettings extends ValueObject implements VehicleListPageSettingsData
{
    public const float IMAGE_SCALE_DEFAULT = 1.0;
    public const int IMAGE_OFFSET_X_DEFAULT = 0;
    public const int IMAGE_OFFSET_Y_DEFAULT = 0;

    private bool $cardLarge = false;
    private bool $imageCovered = false;
    private bool $imageScaled = false;
    private float $imageScale = self::IMAGE_SCALE_DEFAULT;
    private bool $imageOffsetted = false;
    private int $imageOffsetX = self::IMAGE_OFFSET_X_DEFAULT;
    private int $imageOffsetY = self::IMAGE_OFFSET_Y_DEFAULT;

    public function isCardLarge(): bool
    {
        return $this->cardLarge;
    }

    public function setCardLarge(bool $cardLarge): void
    {
        $this->cardLarge = $cardLarge;
    }

    public function isImageCovered(): bool
    {
        return $this->imageCovered;
    }

    public function setImageCovered(bool $imageCovered): void
    {
        $this->imageCovered = $imageCovered;
    }

    public function isImageScaled(): bool
    {
        return $this->imageScaled;
    }

    public function setImageScaled(bool $imageScaled): void
    {
        $this->imageScaled = $imageScaled;
    }

    public function getImageScale(): float
    {
        return $this->imageScale;
    }

    public function setImageScale(float $imageScale): void
    {
        $this->imageScale = $imageScale;
    }

    public function isImageOffsetted(): bool
    {
        return $this->imageOffsetted;
    }

    public function setImageOffsetted(bool $imageOffsetted): void
    {
        $this->imageOffsetted = $imageOffsetted;
    }

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

    public static function hydrate(array $data): static
    {
        $result = new static();

        if (isset($data['cardLarge'])) {
            $result->setCardLarge((bool) $data['cardLarge']);
        }

        if (isset($data['imageCovered'])) {
            $result->setImageCovered((bool) $data['imageCovered']);
        }

        if (isset($data['imageScaled'])) {
            $result->setImageScaled((bool) $data['imageScaled']);
        }

        if (isset($data['imageScale'])) {
            $result->setImageScale((float) $data['imageScale']);
        }

        if (isset($data['imageOffsetted'])) {
            $result->setImageOffsetted((bool) $data['imageOffsetted']);
        }

        if (isset($data['imageOffsetX'])) {
            $result->setImageOffsetX((int) $data['imageOffsetX']);
        }

        if (isset($data['imageOffsetY'])) {
            $result->setImageOffsetY((int)$data['imageOffsetY']);
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            'cardLarge' => $this->isCardLarge(),
            'imageCovered' => $this->isImageCovered(),
            'imageScaled' => $this->isImageScaled(),
            'imageScale' => $this->getImageScale(),
            'imageOffsetted' => $this->isImageOffsetted(),
            'imageOffsetX' => $this->getImageOffsetX(),
            'imageOffsetY' => $this->getImageOffsetY(),
        ];
    }
}

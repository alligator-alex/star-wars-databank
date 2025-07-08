<?php

declare(strict_types=1);

namespace App\Modules\Databank\Public\Helpers;

use App\Modules\Databank\Common\Contracts\DetailPageSettingsData;
use App\Modules\Databank\Common\Contracts\IndexPageSettingsData;
use App\Modules\Databank\Common\DTOs\PageSettings\DetailPagePageSettings;

class ViewHelper
{
    public static function imagePlaceholderRandomSuffix(): string
    {
        $suffixes = [
            '',
            '-fv',
            '-rev',
            '-rev-fv',
        ];

        return $suffixes[array_rand($suffixes)];
    }

    public static function indexPageSettingsImageStyle(IndexPageSettingsData $settings): string
    {
        $imgStyles = [];

        if ($settings->isImageScaled() && ($settings->getImageScale() !== 1.0)) {
            $scalePct = ceil($settings->getImageScale() * 100);

            $imgStyles[] = 'height: ' . $scalePct . '%';
            $imgStyles[] = 'width: ' . $scalePct . '%';
        }

        if ($settings->isImageScaled()) {
            $defaultOffset = -50;

            $offsetX = $defaultOffset - $settings->getImageOffsetX();
            $offsetY = $defaultOffset - $settings->getImageOffsetY();

            $imgStyles[] = 'transform: translate(' . $offsetX . '%, ' . $offsetY . '%)';
        } else {
            $multiplier = -5;

            $offsetX = $multiplier * $settings->getImageOffsetX();
            $offsetY = $multiplier * $settings->getImageOffsetY();

            $imgStyles[] = 'object-position: calc(50% + ' . $offsetX . 'px) calc(50% + ' . $offsetY . 'px)';
        }

        return implode(';', $imgStyles);
    }

    public static function detailPageSettingsImageStyle(DetailPageSettingsData $settings): string
    {
        $imageStyle = '';
        if ($settings->getImageOffsetY() !== DetailPagePageSettings::IMAGE_OFFSET_Y_DEFAULT) {
            $imageStyle .= 'top: ' . $settings->getImageOffsetY() . '%;';
        }

        if ($settings->getImageMaxHeight() !== DetailPagePageSettings::IMAGE_MAX_HEIGHT_DEFAULT) {
            $imageStyle .= 'max-height: ' . $settings->getImageMaxHeight() . 'vh;';
        }

        return $imageStyle;
    }
}

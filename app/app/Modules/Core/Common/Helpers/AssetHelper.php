<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Helpers;

class AssetHelper
{
    public static function assetUrl(string $url): string
    {
        $file = public_path($url);

        return $url . '?' . hash_file('crc32b', $file);
    }
}

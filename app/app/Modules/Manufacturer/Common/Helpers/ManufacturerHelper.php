<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Common\Helpers;

class ManufacturerHelper
{
    /**
     * Combine same manufacturers with different names.
     *
     * @param string $name
     *
     * @return string
     */
    public static function combineSimilar(string $name): string
    {
        return match (mb_strtolower($name)) {
            'cygnus spaceworks' => 'Cygnus Space Workshops',
            'haor chall egineering' => 'Haor Chall Engineering Corporation',
            'hoersch-kessel drive, inc.' => 'Hoersch-Kessel Drive Inc.',
            default => $name,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Faction\Common\Helpers;

class FactionHelper
{
    private const array MAJOR_FACTIONS = [
        'Alliance to Restore the Republic',
        'Confederacy of Independent Systems',
        'First Order',
        'Galactic Empire',
        'Galactic Republic',
        'Jedi Order',
        'New Republic',
        'Resistance',
        'Sith',
    ];

    /**
     * There are many factions in the Galaxy...but only a few of them really matter.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function isMajor(string $name): bool
    {
        $name = mb_strtolower($name);

        return array_any(self::MAJOR_FACTIONS, static fn (string $faction) => mb_strtolower($faction) === $name);
    }
}

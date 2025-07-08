<?php

declare(strict_types=1);

namespace App\Modules\Media\Common\Helpers;

class MediaHelper
{
    /** @var string[] */
    private const array MAJOR_MEDIA = [
        // Prequel trilogy
        'Star Wars: Episode I The Phantom Menace',
        'Star Wars: Episode II Attack of the Clones',
        'Star Wars: Episode III Revenge of the Sith',

        // Original trilogy
        'Star Wars: Episode IV A New Hope',
        'Star Wars: Episode V The Empire Strikes Back',
        'Star Wars: Episode VI Return of the Jedi',

        // Sequel trilogy
        'Star Wars: Episode VII The Force Awakens',
        'Star Wars: Episode VIII The Last Jedi',
        'Star Wars: Episode IX The Rise of Skywalker',

        // Spin-off films
        'Rogue One: A Star Wars Story',
        'Solo: A Star Wars Story',

        // Series
        'Ahsoka',
        'Andor',
        'Obi-Wan Kenobi',
        'The Book of Boba Fett',
        'The Mandalorian',

        // Games
        'Star Wars: Jedi Knight II: Jedi Outcast',
        'Star Wars: Jedi Knight: Jedi Academy',
        'Star Wars: Empire at War',
        'Star Wars: Republic Commando',
        'Star Wars: The Force Unleashed',
        'Star Wars: The Force Unleashed II',
        'Star Wars: Battlefront', // Pandemic Studios classic
        'Star Wars: Battlefront II',
        'Star Wars Battlefront', // DICE remake
        'Star Wars Battlefront II',
        'Star Wars: Squadrons',
        'Star Wars Jedi: Fallen Order',
        'Star Wars Jedi: Survivor',
        'Star Wars Outlaws',
    ];

    /**
     * Follow the main films timeline (32 BBY - 35 ABY)
     * And we really don't want to import vehicles from every series, books or novels (or Acolyte).
     *
     * @param string $name
     *
     * @return bool
     */
    public static function isMajor(string $name): bool
    {
        $name = mb_strtolower($name);

        return array_any(self::MAJOR_MEDIA, static fn (string $mediaName) => mb_strtolower($mediaName) === $name);
    }
}

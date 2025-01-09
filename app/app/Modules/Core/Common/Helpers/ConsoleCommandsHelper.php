<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Helpers;

class ConsoleCommandsHelper
{
    /**
     * Get commands paths inside modules.
     *
     * @return array<int, string>
     */
    public static function getPathsInsideModules(): array
    {
        $modulesPath = base_path('app/Modules');
        if (!is_dir($modulesPath)) {
            return [];
        }

        $dirs = scandir($modulesPath);
        if (!$dirs) {
            return [];
        }

        $modules = array_filter($dirs, static function (string $path): bool {
            return !in_array($path, ['.', '..']);
        });

        return array_values(array_map(static function (string $module) use ($modulesPath): string {
            return $modulesPath
                . DIRECTORY_SEPARATOR . $module
                . DIRECTORY_SEPARATOR . 'Console'
                . DIRECTORY_SEPARATOR . 'Commands';
        }, $modules));
    }
}

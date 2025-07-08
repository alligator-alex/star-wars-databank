<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Core\Common\Helpers;

use App\Modules\Core\Common\Helpers\ConsoleCommandsHelper;
use Tests\TestCase;

class ConsoleCommandsHelperTest extends TestCase
{
    public function testGetPathsInsideModules(): void
    {
        $result = ConsoleCommandsHelper::getPathsInsideModules();

        $modules = [
            'Core',
            'Databank',
            'Droid',
            'Faction',
            'Handbook',
            'Manufacturer',
            'Media',
            'MessageBroker',
            'Sitemap',
            'Vehicle',
        ];

        $this->assertCount(count($modules), $result);

        foreach ($modules as $key => $module) {
            $this->assertEquals(app_path('Modules'
                . DIRECTORY_SEPARATOR . $module
                . DIRECTORY_SEPARATOR . 'Console'
                . DIRECTORY_SEPARATOR . 'Commands'), $result[$key]);
        }
    }
}

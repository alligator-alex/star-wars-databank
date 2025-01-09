<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Core\Common\Helpers;

use App\Modules\Core\Common\Helpers\ConsoleCommandsHelper;
use Tests\TestCase;

class ConsoleCommandsHelperTest extends TestCase
{
    public function testCanGetPathsInsideModules(): void
    {
        $result = ConsoleCommandsHelper::getPathsInsideModules();

        $this->assertCount(3, $result);

        $this->assertEquals('/var/www/app/app/Modules/Core/Console/Commands', $result[0]);
        $this->assertEquals('/var/www/app/app/Modules/Databank/Console/Commands', $result[1]);
        $this->assertEquals('/var/www/app/app/Modules/MessageBroker/Console/Commands', $result[2]);
    }
}

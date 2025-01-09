<?php

declare(strict_types=1);

namespace App\Modules\MessageBroker\Common\Components;

use App\Modules\Core\Common\Components\FileLogger;

class ConsumerLogger extends FileLogger
{
    public function getFileNameWithoutExtension(): string
    {
        return 'consumer';
    }
}

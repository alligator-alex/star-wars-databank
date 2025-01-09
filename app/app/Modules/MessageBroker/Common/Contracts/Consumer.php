<?php

declare(strict_types=1);

namespace App\Modules\MessageBroker\Common\Contracts;

use Generator;

interface Consumer
{
    /**
     * Get messages from consumer.
     *
     * @return Generator<string>
     */
    public function getMessages(): Generator;
}

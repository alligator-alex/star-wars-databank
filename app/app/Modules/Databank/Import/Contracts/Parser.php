<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\Contracts;

use App\Modules\Databank\Import\DTOs\Vehicle;
use App\Modules\Databank\Import\Exceptions\ParserException;

interface Parser
{
    /**
     * @param iterable<string> $items
     *
     * @return iterable<Vehicle>
     *
     * @throws ParserException
     */
    public function parse(iterable $items): iterable;
}

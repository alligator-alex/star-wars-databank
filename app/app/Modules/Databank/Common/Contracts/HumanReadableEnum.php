<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Contracts;

interface HumanReadableEnum
{
    public function nameForHumans(): string;
}

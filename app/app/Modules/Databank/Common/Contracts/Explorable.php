<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Contracts;

use App\Modules\Databank\Public\Enums\ExploreRootType;

interface Explorable
{
    public function explorableKey(): string;
    public function explorableType(): ExploreRootType;
}

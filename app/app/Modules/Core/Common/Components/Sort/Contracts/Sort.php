<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Components\Sort\Contracts;

use App\Modules\Core\Common\Components\Sort\Enums\SortDirection;

interface Sort
{
    public function getAttribute(): ?string;
    public function getDirection(): ?SortDirection;
}

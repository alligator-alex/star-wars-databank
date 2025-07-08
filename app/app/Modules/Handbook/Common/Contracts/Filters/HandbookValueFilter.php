<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Contracts\Filters;

interface HandbookValueFilter
{
    public function getType(): ?int;

    public function getSearchTerm(): ?string;
}

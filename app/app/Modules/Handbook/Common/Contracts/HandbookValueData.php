<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Contracts;

interface HandbookValueData
{
    public function getName(): string;
    public function getSlug(): ?string;
}

<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Contracts\Filters;

interface HandbookFilter
{
    public function getId(): ?int;

    public function getType(): ?int;

    public function getName(): ?string;
}

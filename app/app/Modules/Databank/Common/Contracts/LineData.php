<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Contracts;

use App\Modules\Databank\Common\Enums\Status;

interface LineData
{
    public function getName(): string;
    public function getSlug(): ?string;
    public function getStatus(): Status;
    public function getSort(): ?int;
}

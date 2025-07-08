<?php

declare(strict_types=1);

namespace App\Modules\Media\Common\Contracts;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Media\Common\Enums\MediaType;
use Illuminate\Support\Carbon;

interface MediaData
{
    public function getName(): string;
    public function getSlug(): ?string;
    public function getStatus(): Status;
    public function getSort(): ?int;
    public function getType(): ?MediaType;
    public function getReleaseDate(): ?Carbon;
    public function getPosterId(): ?int;
}

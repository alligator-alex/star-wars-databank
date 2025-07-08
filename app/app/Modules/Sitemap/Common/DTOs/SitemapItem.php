<?php

declare(strict_types=1);

namespace App\Modules\Sitemap\Common\DTOs;

use App\Modules\Sitemap\Common\Enums\ChangeFrequency;
use Illuminate\Support\Carbon;

readonly class SitemapItem
{
    public function __construct(
        private string $loc,
        private ?Carbon $lastMod = null,
        private ?ChangeFrequency $changeFreq = ChangeFrequency::MONTHLY,
        private ?float $priority = 1.0
    ) {
    }

    public function getLoc(): string
    {
        return $this->loc;
    }

    public function getLastMod(): ?Carbon
    {
        return $this->lastMod;
    }

    public function getChangeFreq(): ?ChangeFrequency
    {
        return $this->changeFreq;
    }

    public function getPriority(): ?float
    {
        return $this->priority;
    }
}

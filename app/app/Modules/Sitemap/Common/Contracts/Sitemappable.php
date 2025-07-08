<?php

declare(strict_types=1);

namespace App\Modules\Sitemap\Common\Contracts;

use Illuminate\Support\Carbon;

interface Sitemappable
{
    public function getSitemapUrl(): ?string;
    public function getSitemapModificationDate(): ?Carbon;
}

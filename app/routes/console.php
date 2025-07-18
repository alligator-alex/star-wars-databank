<?php

declare(strict_types=1);

use App\Modules\Sitemap\Console\Commands\BuildSitemap;
use Illuminate\Support\Facades\Schedule;

Schedule::command(BuildSitemap::class)->dailyAt('0:00');

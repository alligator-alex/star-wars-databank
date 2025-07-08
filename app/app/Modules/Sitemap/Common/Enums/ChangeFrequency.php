<?php

declare(strict_types=1);

namespace App\Modules\Sitemap\Common\Enums;

enum ChangeFrequency: string
{
    case ALWAYS = 'always';
    case HOURLY = 'hourly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case NEVER = 'never';
}

<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Enums;

enum CookieName: string
{
    case SKIP_INTRO = 'skip_intro';
    case COOKIE_CONSENT = 'cookie_consent';
}

<?php

declare(strict_types=1);

use App\Modules\Core\Common\Helpers\ConsoleCommandsHelper;
use App\Modules\Databank\Common\Enums\CookieName;
use App\Modules\Sitemap\Console\Commands\BuildSitemap;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        health: '/up',
    )
    ->withCommands(ConsoleCommandsHelper::getPathsInsideModules())
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: [
            CookieName::SKIP_INTRO->value,
            CookieName::COOKIE_CONSENT->value,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->call(BuildSitemap::class)->dailyAt('0:00');
    })
    ->create();

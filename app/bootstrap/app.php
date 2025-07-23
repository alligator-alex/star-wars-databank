<?php

declare(strict_types=1);

use App\Modules\Core\Common\Helpers\ConsoleCommandsHelper;
use App\Modules\Databank\Common\Enums\CookieName;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__ . '/../routes/web.php',
            __DIR__ . '/../routes/breadcrumbs/web.php',
            __DIR__ . '/../routes/breadcrumbs/platform.php',
        ],
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withCommands(ConsoleCommandsHelper::getPathsInsideModules())
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: [
            CookieName::SKIP_INTRO->value,
            CookieName::COOKIE_CONSENT->value,
        ]);
    })
    ->withExceptions()
    ->create();

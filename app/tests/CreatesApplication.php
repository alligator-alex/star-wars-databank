<?php

namespace Tests;

use Artisan;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    public function createApplication(): Application
    {
        $app = $this->makeApplication();

        if (!file_exists(base_path('.env.testing'))) {
            exit('To run tests you must create .env.testing file' . PHP_EOL);
        }

        if (!$this->isTestingEnvironment()) {
            Artisan::call('config:clear');

            $app = $this->makeApplication();
        }

        if (!$this->isTestingEnvironment()) {
            exit('You can run tests only on "testing" environment to avoid corrupting database' . PHP_EOL);
        }

        return $app;
    }

    private function makeApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    private function isTestingEnvironment(): bool
    {
        return config('app.env') === 'testing';
    }
}

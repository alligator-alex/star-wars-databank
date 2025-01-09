<?php

declare(strict_types=1);

namespace Tests;

use Artisan;
use Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public bool $setupDatabase = true;
    protected Faker\Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        if ($this->setupDatabase) {
            $this->setupDatabase();
        }

        $this->faker = Faker\Factory::create();
    }

    private function setupDatabase(): void
    {
        Artisan::call('migrate');

        $this->setupDatabase = false;
    }
}

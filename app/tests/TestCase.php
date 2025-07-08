<?php

declare(strict_types=1);

namespace Tests;

use Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    protected const string HEADING_CLASS_NAME = 'wow fadeInUp';

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->faker = Faker\Factory::create();
    }
}

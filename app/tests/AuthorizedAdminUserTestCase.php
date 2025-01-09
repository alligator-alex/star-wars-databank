<?php

namespace Tests;

use App\Modules\Core\Common\Models\User;
use Illuminate\Support\Facades\Artisan;

abstract class AuthorizedAdminUserTestCase extends TestCase
{
    protected const string HEADING_CLASS_NAME = 'm-0 fw-light h3 text-body-emphasis';

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createTestUser();

        $this->actingAs($this->user);
    }

    protected function createTestUser(?string $name = null, ?string $email = null, ?string $password = null): User
    {
        if (!$name) {
            $name = $this->faker->firstName;
        }

        if (!$email) {
            $email = $this->faker->unique()->safeEmail;
        }

        if (!$password) {
            $password = (string) $this->faker->randomNumber();
        }

        Artisan::call("orchid:admin {$name} {$email} {$password}");

        return User::whereEmail($email)->first();
    }
}

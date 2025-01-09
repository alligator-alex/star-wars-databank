<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Core\Admin\Controllers;

use App\Modules\Core\Admin\Enums\UserRouteName;
use App\Modules\Core\Common\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class UserScreenTest extends AuthorizedAdminUserTestCase
{
    public function testCanVisitProfilePage(): void
    {
        $uri = route(UserRouteName::PROFILE);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">Hello there</h1>',
            $response->content()
        );
    }

    public function testCanUpdate(): void
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];

        $uri = route(UserRouteName::UPDATE);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->user->refresh();

        $this->assertEquals($data['name'], $this->user->name);
        $this->assertEquals($data['email'], $this->user->email);
    }

    public function testCanChangePassword(): void
    {
        $password = (string) $this->faker->randomNumber();

        $user = $this->createTestUser(password: $password);
        $this->actingAs($user);

        $newPassword = (string) $this->faker->randomNumber();

        $data = [
            'currentPassword' => $password,
            'newPassword' => $newPassword,
            'newPassword_confirmation' => $newPassword,
        ];

        $uri = route(UserRouteName::CHANGE_PASSWORD);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $newPasswordHash = DB::table(User::tableName())
            ->select('password')
            ->where('id', '=', $user->id)
            ->value('password');

        $this->assertTrue(Hash::check($newPassword, $newPasswordHash));
    }
}

<?php

namespace Tests\Feature\Modules\Databank\Public\Controllers;

use App\Modules\Databank\Common\Models\Vehicle;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class HomePageControllerTest extends TestCase
{
    public function testCanVisitHomePage(): void
    {
        $vehicles = Vehicle::factory()
            ->count(7)
            ->createMany();

        $uri = '/';
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            __('All :count vehicles', ['count' => $vehicles->count()]),
            $response->content()
        );
    }
}

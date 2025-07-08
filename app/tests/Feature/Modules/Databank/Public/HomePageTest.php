<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Databank\Public;

use App\Modules\Databank\Public\Enums\DatabankRouteName;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function testVisitHomePage(): void
    {
        $uri = route(DatabankRouteName::HOME);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Explore') . '</h1>',
            $response->content()
        );
    }
}

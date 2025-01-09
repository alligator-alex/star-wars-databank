<?php

namespace Tests\Feature\Modules\Databank\Public\Controllers;

use App\Modules\Databank\Common\Enums\VehicleType;
use App\Modules\Databank\Common\Models\Faction;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Models\Media;
use App\Modules\Databank\Public\Enums\VehicleRouteName;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class VehicleControllerTest extends TestCase
{
    public function testCanVisitListPage(): void
    {
        $vehicles = Vehicle::factory()
            ->count(5)
            ->createMany();

        $uri = route(VehicleRouteName::LIST);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        /** @var Vehicle $vehicle */
        $vehicle = $vehicles->random()->first();

        $this->assertStringContainsString(
            '<a href="' . route(VehicleRouteName::ONE, $vehicle->slug, false) . '"',
            $response->content()
        );

        // test pagination
        $uri = route(VehicleRouteName::LIST, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('Nothing found'), $response->content());

        // test json response
        $uri = route(VehicleRouteName::LIST);
        $response = $this->get($uri, ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(Response::HTTP_OK);

        $this->assertJson($response->content());
    }

    public function testCanApplyFiltersOnListPage(): void
    {
        $vehicleA = Vehicle::factory()->createOne([
            'name' => 'LAAT/i',
            'type' => VehicleType::GUNSHIP->value,
        ]);

        $vehicleA->manufacturers()->attach(Manufacturer::factory()->createOne());
        $vehicleA->factions()->attach(Faction::factory()->createOne());
        $vehicleA->appearances()->attach(Media::factory()->createOne());

        $vehicleB = Vehicle::factory()->createOne([
            'name' => 'T-65B X-wing starfighter',
            'type' => VehicleType::STARFIGHTER->value,
        ]);

        $uri = route(VehicleRouteName::LIST);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<a href="' . route(VehicleRouteName::ONE, $vehicleA->slug, false) . '"',
            $response->content()
        );
        $this->assertStringContainsString(
            '<a href="' . route(VehicleRouteName::ONE, $vehicleB->slug, false) . '"',
            $response->content()
        );

        $uri = route(VehicleRouteName::LIST, [
            'category' => [Str::slug($vehicleA->category->nameForHumans())],
            'type' => [Str::slug($vehicleA->type->nameForHumans())],
            'manufacturer' => [$vehicleA->manufacturers->first()->slug],
            'faction' => [$vehicleA->factions->first()->slug],
            'line' => [$vehicleA->line->slug],
            'media' => [$vehicleA->appearances->first()->slug],
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<a href="' . route(VehicleRouteName::ONE, $vehicleA->slug, false) . '"',
            $response->content()
        );
        $this->assertStringNotContainsString(
            '<a href="' . route(VehicleRouteName::ONE, $vehicleB->slug, false) . '"',
            $response->content()
        );

        $uri = route(VehicleRouteName::LIST, [
            'type' => [Str::slug(VehicleType::OTHER->nameForHumans())]
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('Nothing found'), $response->content());
    }

    public function testCanVisitOnePage(): void
    {
        $vehicle = Vehicle::factory()->createOne();

        $uri = route(VehicleRouteName::ONE, $vehicle->slug);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString("<h1>{$vehicle->name}</h1>", $response->content());

        $uri = route(VehicleRouteName::ONE, 'some-non-existing-slug');
        $this->get($uri)->assertStatus(Response::HTTP_NOT_FOUND);
    }
}

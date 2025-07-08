<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Vehicle\Public;

use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    public function testVisitIndexPage(): void
    {
        $vehicles = Vehicle::factory()
            ->count(5)
            ->createMany();

        $uri = route(VehicleRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        /** @var Vehicle $vehicle */
        $vehicle = $vehicles->random()->first();

        $this->assertStringContainsString(
            '<a href="' . route(VehicleRouteName::DETAIL, $vehicle->slug, false) . '"',
            $response->content()
        );

        // test pagination
        $uri = route(VehicleRouteName::INDEX, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('Nothing found'), $response->content());

        // test json response
        $uri = route(VehicleRouteName::INDEX);
        $response = $this->get($uri, ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(Response::HTTP_OK);

        $this->assertJson($response->content());
    }

    public function testApplyFiltersOnIndexPage(): void
    {
        $typeA = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_TYPE)
            ->createOne([
                'name' => 'Gunship',
                'slug' => 'gunship',
            ]);

        $typeB = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_TYPE)
            ->createOne([
                'name' => 'Starfighter',
                'slug' => 'starfighter',
            ]);

        $typeC = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_TYPE)
            ->createOne([
                'name' => 'Other',
                'slug' => 'other',
            ]);

        $vehicleA = Vehicle::factory()->createOne([
            'name' => 'LAAT/i',
            'type_id' => $typeA->id,
        ]);

        $vehicleA->manufacturers()->attach(Manufacturer::factory()->createOne());
        $vehicleA->factions()->attach(Faction::factory()->createOne());
        $vehicleA->appearances()->attach(Media::factory()->createOne());

        $vehicleB = Vehicle::factory()->createOne([
            'name' => 'T-65B X-wing starfighter',
            'type_id' => $typeB->id,
        ]);

        $uri = route(VehicleRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<a href="' . route(VehicleRouteName::DETAIL, $vehicleA->slug, false) . '"',
            $response->content()
        );
        $this->assertStringContainsString(
            '<a href="' . route(VehicleRouteName::DETAIL, $vehicleB->slug, false) . '"',
            $response->content()
        );

        $uri = route(VehicleRouteName::INDEX, [
            'categories' => [$vehicleA->category->slug],
            'types' => [$vehicleA->type->slug],
            'lines' => [$vehicleA->line->slug],
            'manufacturers' => [$vehicleA->manufacturers->first()->slug],
            'factions' => [$vehicleA->factions->first()->slug],
            'media' => [$vehicleA->appearances->first()->slug],
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<a href="' . route(VehicleRouteName::DETAIL, $vehicleA->slug, false) . '"',
            $response->content()
        );
        $this->assertStringNotContainsString(
            '<a href="' . route(VehicleRouteName::DETAIL, $vehicleB->slug, false) . '"',
            $response->content()
        );

        $uri = route(VehicleRouteName::INDEX, [
            'types' => [$typeC->slug]
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('Nothing found'), $response->content());
    }

    public function testVisitDetailPage(): void
    {
        $vehicle = Vehicle::factory()->createOne();

        $uri = route(VehicleRouteName::DETAIL, $vehicle->slug);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString("<h1>{$vehicle->name}</h1>", $response->content());

        $uri = route(VehicleRouteName::DETAIL, 'some-non-existing-slug');
        $this->get($uri)->assertStatus(Response::HTTP_NOT_FOUND);
    }
}

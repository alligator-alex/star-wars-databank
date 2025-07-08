<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Vehicle\Admin;

use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\StarshipTechSpecs;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class VehicleTest extends AuthorizedAdminUserTestCase
{
    public function testVisitIndexPage(): void
    {
        /** @var Collection<Vehicle> $vehicles */
        $vehicles = Vehicle::factory()
            ->createMany([
                [
                    'name' => 'LAAT/i',
                ],
                [
                    'name' => 'T-65B X-wing starfighter',
                ],
                [
                    'name' => 'TIE/LN starfighter',
                ],
            ]);

        $uri = route(VehicleRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Vehicles') . '</h1>',
            $response->content()
        );

        foreach ($vehicles as $vehicle) {
            $this->assertStringContainsString(
                '<b>' . Str::limit($vehicle->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
                $response->content()
            );
        }

        $uri = route(VehicleRouteName::INDEX, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testApplyFiltersOnIndexPage(): void
    {
        $vehicleA = Vehicle::factory()->createOne([
            'name' => 'LAAT/i',
        ]);

        $vehicleA->manufacturers()->attach(Manufacturer::factory()->createOne());
        $vehicleA->factions()->attach(Faction::factory()->createOne());

        $vehicleB = Vehicle::factory()->createOne([
            'name' => 'T-65B X-wing starfighter',
        ]);

        $uri = route(VehicleRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Vehicles') . '</h1>',
            $response->content()
        );

        $this->assertStringContainsString(
            '<b>' . Str::limit($vehicleA->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );
        $this->assertStringContainsString(
            '<b>' . Str::limit($vehicleB->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );

        $uri = route(VehicleRouteName::INDEX, [
            'name' => $vehicleA->name,
            'status' => $vehicleA->status->value,
            'categoriesIds' => [$vehicleA->category->value],
            'typesIds' => [$vehicleA->type->value],
            'linesIds' => [$vehicleA->line->id],
            'manufacturersIds' => [$vehicleA->manufacturers->first()->id],
            'factionsIds' => [$vehicleA->factions->first()->id],
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<b>' . Str::limit($vehicleA->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );
        $this->assertStringNotContainsString(
            '<b>' . Str::limit($vehicleB->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );

        $uri = route(VehicleRouteName::INDEX, [
            'name' => 'Unknown',
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testVisitCreatePage(): void
    {
        $uri = route(VehicleRouteName::CREATE);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Create Vehicle') . '</h1>',
            $response->content()
        );
    }

    public function testVisitEditPage(): void
    {
        $vehicle = Vehicle::factory()->createOne([
            'name' => 'T-65B X-wing starfighter',
        ]);

        $uri = route(VehicleRouteName::EDIT, $vehicle->id);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . $vehicle->name . '</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCreate(Closure $getRequestData): void
    {
        /** @var Collection<Vehicle> $vehicles */
        $vehicles = Vehicle::factory()->count(2)->createMany();

        $data = $getRequestData($this);

        $uri = route(VehicleRouteName::STORE);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Vehicle::class, $vehicles->count() + 1);

        $vehicle = Vehicle::whereExternalUrl($data['externalUrl'])->first();

        $this->assertDataEqualsModel($data, $vehicle);
    }

    #[DataProvider('requestData')]
    public function testUpdate(Closure $getRequestData): void
    {
        $category = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_CATEGORY)
            ->createOne([
                'name' => 'Ground',
                'slug' => 'ground',
            ]);

        $type = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_TYPE)
            ->createOne([
                'name' => 'Other',
                'slug' => 'other',
            ]);

        $line = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_LINE)
            ->createOne([
                'name' => 'X-wing',
                'slug' => 'x-wing',
            ]);

        $vehicle = Vehicle::factory()->createOne([
            'name' => 'T-65B X-wing starfighter',
            'status' => Status::DRAFT->value,
            'sort' => $this->faker->randomNumber(3),
            'external_url' => $this->faker->url,
            'category_id' => $category->id,
            'type_id' => $type->id,
            'line_id' => $line->id,
            'image_id' => null,
            'description' => $this->faker->text(1000),
            'technical_specifications' => [],
        ]);

        $data = $getRequestData($this);

        $uri = route(VehicleRouteName::UPDATE, $vehicle->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Vehicle::class, 1);

        $vehicle->refresh();

        $this->assertDataEqualsModel($data, $vehicle);
    }

    public function testUpdateIndexPageSettings(): void
    {
        $vehicle = Vehicle::factory()->createOne();

        $data = [
            'cardLarge' => true,
            'imageCovered' => true,
            'imageScaled' => true,
            'imageScale' => 1.5,
            'imageOffsetted' => true,
            'imageOffsetX' => 20,
            'imageOffsetY' => 30,
        ];

        $uri = route(VehicleRouteName::UPDATE_INDEX_PAGE_SETTINGS, $vehicle->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $vehicle = $vehicle->refresh();

        $pageSettings = $vehicle->getPageSettings()->getForIndex();

        $this->assertEquals($data['cardLarge'], $pageSettings->isCardLarge());
        $this->assertEquals($data['imageCovered'], $pageSettings->isImageCovered());
        $this->assertEquals($data['imageScaled'], $pageSettings->isImageScaled());
        $this->assertEquals($data['imageScale'], $pageSettings->getImageScale());
        $this->assertEquals($data['imageOffsetted'], $pageSettings->isImageOffsetted());
        $this->assertEquals($data['imageOffsetX'], $pageSettings->getImageOffsetX());
        $this->assertEquals($data['imageOffsetY'], $pageSettings->getImageOffsetY());
    }

    public function testUpdateDetailPageSettings(): void
    {
        $vehicle = Vehicle::factory()->createOne();

        $data = [
            'imageOffsetX' => 10,
            'imageOffsetY' => 20,
            'imageMaxHeight' => 30,
        ];

        $uri = route(VehicleRouteName::UPDATE_DETAIL_PAGE_SETTINGS, $vehicle->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $vehicle = $vehicle->refresh();

        $pageSettings = $vehicle->getPageSettings()->getForDetail();

        $this->assertEquals($data['imageOffsetX'], $pageSettings->getImageOffsetX());
        $this->assertEquals($data['imageOffsetY'], $pageSettings->getImageOffsetY());
        $this->assertEquals($data['imageMaxHeight'], $pageSettings->getImageMaxHeight());
    }

    public function testDelete(): void
    {
        $vehicle = Vehicle::factory()->createOne();

        $uri = route(VehicleRouteName::DELETE, $vehicle->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Vehicle::class, 0);
    }

    public function testTogglePublish(): void
    {
        $vehicle = Vehicle::factory()->createOne([
            'status' => Status::DRAFT->value,
        ]);

        $uri = route(VehicleRouteName::TOGGLE_PUBLISH, $vehicle->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $vehicle->refresh();

        $this->assertEquals(Status::PUBLISHED, $vehicle->status);

        $uri = route(VehicleRouteName::TOGGLE_PUBLISH, $vehicle->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $vehicle->refresh();

        $this->assertEquals(Status::DRAFT, $vehicle->status);
    }

    /**
     * @param array<string, mixed> $data
     * @param Vehicle $vehicle
     *
     * @return void
     */
    private function assertDataEqualsModel(array $data, Vehicle $vehicle): void
    {
        $this->assertEquals($data['status'], $vehicle->status->value);
        $this->assertEquals($data['sort'], $vehicle->sort);
        $this->assertEquals($data['externalUrl'], $vehicle->external_url);
        $this->assertEquals($data['categoryId'], $vehicle->category_id);
        $this->assertEquals($data['typeId'], $vehicle->type_id);
        $this->assertEquals($data['lineId'], $vehicle->line_id);

        $this->assertNotEmpty($vehicle->manufacturers);
        $this->assertEquals(
            Arr::sort($data['manufacturersIds']),
            $vehicle->manufacturers->sortBy('id')->pluck('id')->toArray()
        );

        $this->assertNotEmpty($vehicle->factions);
        $this->assertEquals(
            Arr::sort($data['factionsIds']),
            $vehicle->factions->sortBy('id')->pluck('id')->toArray()
        );

        $this->assertNotNull($vehicle->mainFaction);
        $this->assertEquals($data['mainFactionId'], $vehicle->mainFaction->id);

        $this->assertEquals($data['imageId'], $vehicle->image_id);
        $this->assertEquals($data['description'], $vehicle->description);

        $this->assertEquals(json_encode($data['techSpecs']), json_encode($vehicle->getTechnicalSpecifications()));

        $this->assertNotEmpty($vehicle->appearances);
        $this->assertEquals(
            Arr::sort($data['appearancesIds']),
            $vehicle->appearances->sortBy('id')->pluck('id')->toArray()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function requestData(): array
    {
        return [
            'all fields' => [
                static function (self $context): array {
                    $category = HandbookValue::factory()
                        ->ofHandbookType(HandbookType::VEHICLE_CATEGORY)
                        ->createOne([
                            'name' => 'Starship',
                            'slug' => 'starship',
                        ]);

                    $type = HandbookValue::factory()
                        ->ofHandbookType(HandbookType::VEHICLE_TYPE)
                        ->createOne([
                            'name' => 'Starfighter',
                            'slug' => 'starfighter',
                        ]);

                    $line = HandbookValue::factory()
                        ->ofHandbookType(HandbookType::VEHICLE_LINE)
                        ->createOne([
                            'name' => 'X-wing',
                            'slug' => 'x-wing',
                        ]);

                    /** @var int[] $factionsIds */
                    $factionsIds = Faction::factory()
                        ->count(3)
                        ->createMany()
                        ->pluck('id')
                        ->toArray();

                    /** @var int[] $mediaIds */
                    $mediaIds = Media::factory()
                        ->count(2)
                        ->createMany()
                        ->pluck('id')
                        ->toArray();

                    return [
                        'name' => 'T-65B X-wing starfighter',
                        'status' => Status::PUBLISHED->value,
                        'sort' => 100,
                        'externalUrl' => 'https://starwars.fandom.com/wiki/T-65B_X-wing_starfighter',
                        'categoryId' => $category->id,
                        'typeId' => $type->id,
                        'lineId' => $line->id,
                        'manufacturersIds' => Manufacturer::factory()
                            ->count(2)
                            ->createMany()
                            ->pluck('id')
                            ->toArray(),
                        'factionsIds' => $factionsIds,
                        'mainFactionId' => $context->faker->randomElement($factionsIds),
                        'imageId' => Attachment::factory()->createOne()->id,
                        'description' => 'The T-65B X-wing starfighter was a single-seat craft manufactured by Incom Corporation'
                            . ' and used most famously by the Alliance to Restore the Republic during the Galactic Civil War.',
                        'techSpecs' => StarshipTechSpecs::hydrate([
                            'length' => '12.5 meters',
                            'width' => '11 meters',
                            'height' => '4.4 meters',
                            'maxSpeed' => '1,050 kph',
                            'maxAcceleration' => '3,700 G',
                            'mglt' => '100 MGLT',
                            'hyperdriveRating' => 'Class 1',
                        ])->toArray(),
                        'appearancesIds' => $mediaIds,
                    ];
                }],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Databank\Admin\Controllers;

use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Databank\Admin\Components\Vehicle\Layouts\List\ListTable;
use App\Modules\Databank\Admin\Enums\VehicleRouteName;
use App\Modules\Databank\Common\DTOs\TechSpecs\StarshipTechSpecs;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Common\Enums\VehicleType;
use App\Modules\Databank\Common\Models\Faction;
use App\Modules\Databank\Common\Models\Line;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Models\Media;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class VehicleScreensTest extends AuthorizedAdminUserTestCase
{
    public function testCanVisitListPage(): void
    {
        /** @var Collection<Vehicle> $models */
        $models = Vehicle::factory()
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

        $uri = route(VehicleRouteName::LIST);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Vehicles') . '</h1>',
            $response->content()
        );

        foreach ($models as $model) {
            $this->assertStringContainsString(
                '<b>' . Str::limit($model->name, ListTable::NAME_SYMBOL_LIMIT) . '</b>',
                $response->content()
            );
        }

        $uri = route(VehicleRouteName::LIST, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testCanApplyFiltersOnListPage(): void
    {
        $modelA = Vehicle::factory()->createOne([
            'name' => 'LAAT/i',
        ]);

        $modelA->manufacturers()->attach(Manufacturer::factory()->createOne());
        $modelA->factions()->attach(Faction::factory()->createOne());

        $modelB = Vehicle::factory()->createOne([
            'name' => 'T-65B X-wing starfighter',
        ]);

        $uri = route(VehicleRouteName::LIST);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Vehicles') . '</h1>',
            $response->content()
        );

        $this->assertStringContainsString(
            '<b>' . Str::limit($modelA->name, ListTable::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );
        $this->assertStringContainsString(
            '<b>' . Str::limit($modelB->name, ListTable::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );

        $uri = route(VehicleRouteName::LIST, [
            'name' => $modelA->name,
            'status' => $modelA->status->value,
            'category' => [$modelA->category->value],
            'type' => [$modelA->type->value],
            'line' => [$modelA->line->id],
            'manufacturer' => [$modelA->manufacturers->first()->id],
            'faction' => [$modelA->factions->first()->id],
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<b>' . Str::limit($modelA->name, ListTable::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );
        $this->assertStringNotContainsString(
            '<b>' . Str::limit($modelB->name, ListTable::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );

        $uri = route(VehicleRouteName::LIST, [
            'name' => 'Unknown',
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testCanVisitOnePage(): void
    {
        $model = Vehicle::factory()->createOne([
            'name' => 'T-65B X-wing starfighter',
        ]);

        $uri = route(VehicleRouteName::ONE, $model->id);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . $model->name . '</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCanUpdate(Closure $getRequestData): void
    {
        $model = Vehicle::factory()->createOne([
            'name' => 'T-65B X-wing starfighter',
            'status' => Status::DRAFT->value,
            'sort' => $this->faker->randomNumber(3),
            'external_url' => $this->faker->url,
            'category' => VehicleCategory::GROUND->value,
            'type' => VehicleType::OTHER->value,
            'line_id' => Line::factory()->createOne()->id,
            'image_id' => null,
            'description' => $this->faker->text(1000),
            'technical_specifications' => [],
        ]);

        $data = $getRequestData($this);

        $uri = route(VehicleRouteName::UPDATE, $model->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Vehicle::class, 1);

        $model = $model->refresh();

        $this->assertDataEqualsModel($data, $model);
    }

    public function testCanUpdateOnePageSettings(): void
    {
        $model = Vehicle::factory()->createOne();

        $data = [
            'imageOffsetX' => 10,
            'imageOffsetY' => 20,
            'imageMaxHeight' => 30,
        ];

        $uri = route(VehicleRouteName::UPDATE_ONE_PAGE_SETTINGS, $model->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_OK);

        $model = $model->refresh();

        $pageSettings = $model->getPageSettings()->getForDetail();

        $this->assertEquals($data['imageOffsetX'], $pageSettings->getImageOffsetX());
        $this->assertEquals($data['imageOffsetY'], $pageSettings->getImageOffsetY());
        $this->assertEquals($data['imageMaxHeight'], $pageSettings->getImageMaxHeight());
    }

    public function testCanUpdateListPageSettings(): void
    {
        $model = Vehicle::factory()->createOne();

        $data = [
            'cardLarge' => true,
            'imageCovered' => true,
            'imageScaled' => true,
            'imageScale' => 1.5,
            'imageOffsetted' => true,
            'imageOffsetX' => 20,
            'imageOffsetY' => 30,
        ];

        $uri = route(VehicleRouteName::UPDATE_LIST_PAGE_SETTINGS, $model->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_OK);

        $model = $model->refresh();

        $pageSettings = $model->getPageSettings()->getForList();

        $this->assertEquals($data['cardLarge'], $pageSettings->isCardLarge());
        $this->assertEquals($data['imageCovered'], $pageSettings->isImageCovered());
        $this->assertEquals($data['imageScaled'], $pageSettings->isImageScaled());
        $this->assertEquals($data['imageScale'], $pageSettings->getImageScale());
        $this->assertEquals($data['imageOffsetted'], $pageSettings->isImageOffsetted());
        $this->assertEquals($data['imageOffsetX'], $pageSettings->getImageOffsetX());
        $this->assertEquals($data['imageOffsetY'], $pageSettings->getImageOffsetY());
    }

    public function testCanVisitNewPage(): void
    {
        $uri = route(VehicleRouteName::NEW);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Create new Vehicle') . '</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCanCreate(Closure $getRequestData): void
    {
        /** @var Collection<Vehicle> $models */
        $models = Vehicle::factory()->count(2)->createMany();

        $data = $getRequestData($this);

        $uri = route(VehicleRouteName::CREATE);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Vehicle::class, $models->count() + 1);

        $model = Vehicle::whereExternalUrl($data['externalUrl'])->first();

        $this->assertDataEqualsModel($data, $model);
    }

    public function testCanDelete(): void
    {
        $model = Vehicle::factory()->createOne();

        $uri = route(VehicleRouteName::DELETE, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Vehicle::class, 0);
    }

    public function testCanTogglePublish(): void
    {
        $model = Vehicle::factory()->createOne([
            'status' => Status::DRAFT->value,
        ]);

        $uri = route(VehicleRouteName::TOGGLE_PUBLISH, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $model->refresh();

        $this->assertEquals(Status::PUBLISHED, $model->status);

        $uri = route(VehicleRouteName::TOGGLE_PUBLISH, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $model->refresh();

        $this->assertEquals(Status::DRAFT, $model->status);
    }

    /**
     * @param array<string, mixed> $data
     * @param Vehicle $model
     *
     * @return void
     */
    private function assertDataEqualsModel(array $data, Vehicle $model): void
    {
        $this->assertEquals($data['status'], $model->status->value);
        $this->assertEquals($data['sort'], $model->sort);
        $this->assertEquals($data['externalUrl'], $model->external_url);
        $this->assertEquals($data['category'], $model->category->value);
        $this->assertEquals($data['type'], $model->type->value);
        $this->assertEquals($data['lineId'], $model->line_id);

        $this->assertNotEmpty($model->manufacturers);
        $this->assertEquals(
            Arr::sort($data['manufacturersIds']),
            $model->manufacturers->sortBy('id')->pluck('id')->toArray()
        );

        $this->assertNotEmpty($model->factions);
        $this->assertEquals(
            Arr::sort($data['factionsIds']),
            $model->factions->sortBy('id')->pluck('id')->toArray()
        );

        $this->assertNotNull($model->mainFaction);
        $this->assertEquals($data['mainFactionId'], $model->mainFaction->id);

        $this->assertEquals($data['imageId'], $model->image_id);
        $this->assertEquals($data['description'], $model->description);

        $this->assertEquals(json_encode($data['techSpecs']), json_encode($model->getTechnicalSpecifications()));

        $this->assertNotEmpty($model->appearances);
        $this->assertEquals(
            Arr::sort($data['appearancesIds']),
            $model->appearances->sortBy('id')->pluck('id')->toArray()
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
                        'category' => VehicleCategory::STARSHIP->value,
                        'type' => VehicleType::STARFIGHTER->value,
                        'lineId' => Line::factory()->createOne()->id,
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

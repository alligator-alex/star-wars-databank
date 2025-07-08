<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Droid\Admin;

use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Droid\Common\DTOs\TechSpecs;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class DroidTest extends AuthorizedAdminUserTestCase
{
    public function testVisitIndexPage(): void
    {
        /** @var Collection<Droid> $droids */
        $droids = Droid::factory()
            ->createMany([
                [
                    'name' => 'B1-series battle droid',
                ],
                [
                    'name' => '3PO-series protocol droid',
                ],
                [
                    'name' => 'C1-series astromech droid',
                ],
            ]);

        $uri = route(DroidRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Droids') . '</h1>',
            $response->content()
        );

        foreach ($droids as $droid) {
            $this->assertStringContainsString(
                '<b>' . $droid->name . '</b>',
                $response->content()
            );
        }

        $uri = route(DroidRouteName::INDEX, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testApplyFiltersOnIndexPage(): void
    {
        $droidA = Droid::factory()->createOne([
            'name' => '3PO-series protocol droid',
        ]);

        $droidA->manufacturers()->attach(Manufacturer::factory()->createOne());
        $droidA->factions()->attach(Faction::factory()->createOne());

        $droidB = Droid::factory()->createOne([
            'name' => 'B1-series battle droid',
        ]);

        $uri = route(DroidRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Droids') . '</h1>',
            $response->content()
        );

        $this->assertStringContainsString(
            '<b>' . $droidA->name . '</b>',
            $response->content()
        );
        $this->assertStringContainsString(
            '<b>' . $droidB->name . '</b>',
            $response->content()
        );

        $uri = route(DroidRouteName::INDEX, [
            'name' => $droidA->name,
            'status' => $droidA->status->value,
            'categoriesIds' => [$droidA->line->id],
            'modelsIds' => [$droidA->model->id],
            'classesIds' => [$droidA->class->id],
            'manufacturersIds' => [$droidA->manufacturers->first()->id],
            'factionsIds' => [$droidA->factions->first()->id],
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<b>' . $droidA->name . '</b>',
            $response->content()
        );
        $this->assertStringNotContainsString(
            '<b>' . $droidB->name . '</b>',
            $response->content()
        );

        $uri = route(DroidRouteName::INDEX, [
            'name' => 'Unknown',
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testVisitCreatePage(): void
    {
        $uri = route(DroidRouteName::CREATE);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Create Droid') . '</h1>',
            $response->content()
        );
    }

    public function testVisitEditPage(): void
    {
        $droid = Droid::factory()->createOne([
            'name' => 'B1-series battle droid',
        ]);

        $uri = route(DroidRouteName::EDIT, $droid->id);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . $droid->name . '</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCreate(Closure $getRequestData): void
    {
        /** @var Collection<Droid> $droids */
        $droids = Droid::factory()->count(2)->createMany();

        $data = $getRequestData($this);

        $uri = route(DroidRouteName::STORE);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Droid::class, $droids->count() + 1);

        $droid = Droid::whereExternalUrl($data['externalUrl'])->first();

        $this->assertDataEqualsModel($data, $droid);
    }

    #[DataProvider('requestData')]
    public function testUpdate(Closure $getRequestData): void
    {
        $line = HandbookValue::factory()
            ->ofHandbookType(HandbookType::DROID_LINE)
            ->createOne([
                'name' => '3PO',
                'slug' => '3po',
            ]);

        $model = HandbookValue::factory()
            ->ofHandbookType(HandbookType::DROID_MODEL)
            ->createOne([
                'name' => 'C3-3PO',
                'slug' => 'c3-po',
            ]);

        $class = HandbookValue::factory()
            ->ofHandbookType(HandbookType::DROID_CLASS)
            ->createOne([
                'name' => 'Protocol droids',
                'slug' => 'protocol-droids',
            ]);

        $droid = Droid::factory()->createOne([
            'name' => '3PO-series protocol droid',
            'status' => Status::DRAFT->value,
            'sort' => $this->faker->randomNumber(3),
            'external_url' => $this->faker->url,
            'line_id' => $line->id,
            'model_id' => $model->id,
            'class_id' => $class->id,
            'image_id' => null,
            'description' => $this->faker->text(1000),
            'technical_specifications' => [],
        ]);

        $data = $getRequestData($this);

        $uri = route(DroidRouteName::UPDATE, $droid->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Droid::class, 1);

        $droid = $droid->refresh();

        $this->assertDataEqualsModel($data, $droid);
    }

    public function testUpdateIndexPageSettings(): void
    {
        $droid = Droid::factory()->createOne();

        $data = [
            'cardLarge' => true,
            'imageCovered' => true,
            'imageScaled' => true,
            'imageScale' => 1.5,
            'imageOffsetted' => true,
            'imageOffsetX' => 20,
            'imageOffsetY' => 30,
        ];

        $uri = route(DroidRouteName::UPDATE_INDEX_PAGE_SETTINGS, $droid->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $droid = $droid->refresh();

        $pageSettings = $droid->getPageSettings()->getForIndex();

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
        $droid = Droid::factory()->createOne();

        $data = [
            'imageOffsetX' => 10,
            'imageOffsetY' => 20,
            'imageMaxHeight' => 30,
        ];

        $uri = route(DroidRouteName::UPDATE_DETAIL_PAGE_SETTINGS, $droid->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $droid = $droid->refresh();

        $pageSettings = $droid->getPageSettings()->getForDetail();

        $this->assertEquals($data['imageOffsetX'], $pageSettings->getImageOffsetX());
        $this->assertEquals($data['imageOffsetY'], $pageSettings->getImageOffsetY());
        $this->assertEquals($data['imageMaxHeight'], $pageSettings->getImageMaxHeight());
    }

    public function testDelete(): void
    {
        $droid = Droid::factory()->createOne();

        $uri = route(DroidRouteName::DELETE, $droid->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Droid::class, 0);
    }

    public function testTogglePublish(): void
    {
        $droid = Droid::factory()->createOne([
            'status' => Status::DRAFT->value,
        ]);

        $uri = route(DroidRouteName::TOGGLE_PUBLISH, $droid->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $droid->refresh();

        $this->assertEquals(Status::PUBLISHED, $droid->status);

        $uri = route(DroidRouteName::TOGGLE_PUBLISH, $droid->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $droid->refresh();

        $this->assertEquals(Status::DRAFT, $droid->status);
    }

    /**
     * @param array<string, mixed> $data
     * @param Droid $droid
     *
     * @return void
     */
    private function assertDataEqualsModel(array $data, Droid $droid): void
    {
        $this->assertEquals($data['status'], $droid->status->value);
        $this->assertEquals($data['sort'], $droid->sort);
        $this->assertEquals($data['externalUrl'], $droid->external_url);
        $this->assertEquals($data['lineId'], $droid->line_id);
        $this->assertEquals($data['modelId'], $droid->model_id);
        $this->assertEquals($data['classId'], $droid->class_id);

        $this->assertNotEmpty($droid->manufacturers);
        $this->assertEquals(
            Arr::sort($data['manufacturersIds']),
            $droid->manufacturers->sortBy('id')->pluck('id')->toArray()
        );

        $this->assertNotEmpty($droid->factions);
        $this->assertEquals(
            Arr::sort($data['factionsIds']),
            $droid->factions->sortBy('id')->pluck('id')->toArray()
        );

        $this->assertNotNull($droid->mainFaction);
        $this->assertEquals($data['mainFactionId'], $droid->mainFaction->id);

        $this->assertEquals($data['imageId'], $droid->image_id);
        $this->assertEquals($data['description'], $droid->description);

        $this->assertEquals(json_encode($data['techSpecs']), json_encode($droid->getTechnicalSpecifications()));

        $this->assertNotEmpty($droid->appearances);
        $this->assertEquals(
            Arr::sort($data['appearancesIds']),
            $droid->appearances->sortBy('id')->pluck('id')->toArray()
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
                    $line = HandbookValue::factory()
                        ->ofHandbookType(HandbookType::DROID_LINE)
                        ->createOne([
                            'name' => '3PO',
                            'slug' => '3po',
                        ]);

                    $model = HandbookValue::factory()
                        ->ofHandbookType(HandbookType::DROID_MODEL)
                        ->createOne([
                            'name' => 'C3-3PO',
                            'slug' => 'c3-po',
                        ]);

                    $class = HandbookValue::factory()
                        ->ofHandbookType(HandbookType::DROID_CLASS)
                        ->createOne([
                            'name' => 'Protocol droids',
                            'slug' => 'protocol-droids',
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
                        'name' => '3PO-series protocol droid',
                        'status' => Status::PUBLISHED->value,
                        'sort' => 100,
                        'externalUrl' => 'https://starwars.fandom.com/wiki/C-3PO',
                        'lineId' => $line->id,
                        'modelId' => $model->id,
                        'classId' => $class->id,
                        'manufacturersIds' => Manufacturer::factory()
                            ->count(2)
                            ->createMany()
                            ->pluck('id')
                            ->toArray(),
                        'factionsIds' => $factionsIds,
                        'mainFactionId' => $context->faker->randomElement($factionsIds),
                        'imageId' => Attachment::factory()->createOne()->id,
                        'description' => 'C-3PO (See-Threepio) was a 3PO-series protocol droid designed to interact'
                            . ' with organics, programmed primarily for etiquette and protocol.',
                        'techSpecs' => TechSpecs::hydrate([
                            'height' => '1.77 meters',
                            'mass' => '75 kilograms',
                            'gender' => 'Masculine programming',
                        ])->toArray(),
                        'appearancesIds' => $mediaIds,
                    ];
                }],
        ];
    }
}

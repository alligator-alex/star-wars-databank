<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Databank\Admin\Controllers;

use App\Modules\Databank\Admin\Components\Manufacturer\Layouts\List\ListTable;
use App\Modules\Databank\Admin\Enums\ManufacturerRouteName;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Models\Manufacturer;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class ManufacturerScreensTest extends AuthorizedAdminUserTestCase
{
    public function testCanVisitListPage(): void
    {
        /** @var Collection<Manufacturer> $models */
        $models = Manufacturer::factory()
            ->createMany([
                [
                    'name' => 'Kuat Drive Yards',
                ],
                [
                    'name' => 'Incom Corporation',
                ],
            ]);

        $uri = route(ManufacturerRouteName::LIST);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Manufacturers') . '</h1>',
            $response->content()
        );

        foreach ($models as $model) {
            $this->assertStringContainsString(
                '<b>' . Str::limit($model->name, ListTable::NAME_SYMBOL_LIMIT) . '</b>',
                $response->content()
            );
        }

        $uri = route(ManufacturerRouteName::LIST, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testCanApplyFiltersOnListPage(): void
    {
        $modelA = Manufacturer::factory()->createOne([
            'name' => 'Kuat Drive Yards',
        ]);

        $modelB = Manufacturer::factory()->createOne([
            'name' => 'Incom Corporation',
        ]);

        $uri = route(ManufacturerRouteName::LIST);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Manufacturers') . '</h1>',
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

        $uri = route(ManufacturerRouteName::LIST, [
            'name' => $modelA->name,
            'status' => $modelA->status->value,
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

        $uri = route(ManufacturerRouteName::LIST, [
            'name' => 'Unknown',
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testCanVisitOnePage(): void
    {
        $model = Manufacturer::factory()->createOne([
            'name' => 'Incom Corporation',
        ]);

        $uri = route(ManufacturerRouteName::ONE, $model->id);
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
        $model = Manufacturer::factory()->createOne([
            'name' => 'Acme Corporation',
            'status' => Status::DRAFT->value,
            'sort' => $this->faker->randomNumber(3),
        ]);

        $data = $getRequestData();

        $uri = route(ManufacturerRouteName::UPDATE, $model->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Manufacturer::class, 1);

        $model->refresh();

        $this->assertDataEqualsModel($data, $model);
    }

    public function testCanVisitNewPage(): void
    {
        $uri = route(ManufacturerRouteName::NEW);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Create new Manufacturer') . '</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCanCreate(Closure $getRequestData): void
    {
        /** @var Collection<Manufacturer> $models */
        $models = Manufacturer::factory()->count(2)->createMany();

        $data = $getRequestData();

        $uri = route(ManufacturerRouteName::CREATE);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Manufacturer::class, $models->count() + 1);

        $model = Manufacturer::whereName($data['name'])->first();

        $this->assertDataEqualsModel($data, $model);
    }

    public function testCanDelete(): void
    {
        $model = Manufacturer::factory()->createOne();

        $uri = route(ManufacturerRouteName::DELETE, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Manufacturer::class, 0);
    }

    public function testCanTogglePublish(): void
    {
        $model = Manufacturer::factory()->createOne([
            'status' => Status::DRAFT->value,
        ]);

        $uri = route(ManufacturerRouteName::TOGGLE_PUBLISH, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $model->refresh();

        $this->assertEquals(Status::PUBLISHED, $model->status);

        $uri = route(ManufacturerRouteName::TOGGLE_PUBLISH, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $model->refresh();

        $this->assertEquals(Status::DRAFT, $model->status);
    }

    /**
     * @param array<string, mixed> $data
     * @param Manufacturer $model
     *
     * @return void
     */
    private function assertDataEqualsModel(array $data, Manufacturer $model): void
    {
        $this->assertEquals($data['name'], $model->name);

        if (isset($data['slug'])) {
            $this->assertEquals($data['slug'], $model->slug);
        }

        $this->assertEquals($data['status'], $model->status->value);

        if (isset($data['sort'])) {
            $this->assertEquals($data['sort'], $model->sort);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function requestData(): array
    {
        return [
            'all fields' => [
                static fn (): array => [
                    'name' => 'Incom Corporation',
                    'slug' => 'incom-corporation',
                    'status' => Status::PUBLISHED->value,
                    'sort' => 100,
                ],
            ],
            'required fields only' => [
                static fn (): array => [
                    'name' => 'Incom Corporation',
                    'status' => Status::PUBLISHED->value,
                ],
            ],
        ];
    }
}

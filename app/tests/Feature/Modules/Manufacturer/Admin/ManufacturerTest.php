<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Manufacturer\Admin;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Manufacturer\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Manufacturer\Admin\Enums\ManufacturerRouteName;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class ManufacturerTest extends AuthorizedAdminUserTestCase
{
    public function testVisitIndexPage(): void
    {
        /** @var Collection<Manufacturer> $manufacturers */
        $manufacturers = Manufacturer::factory()
            ->createMany([
                [
                    'name' => 'Kuat Drive Yards',
                ],
                [
                    'name' => 'Incom Corporation',
                ],
            ]);

        $uri = route(ManufacturerRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Manufacturers') . '</h1>',
            $response->content()
        );

        foreach ($manufacturers as $manufacturer) {
            $this->assertStringContainsString(
                '<b>' . Str::limit($manufacturer->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
                $response->content()
            );
        }

        $uri = route(ManufacturerRouteName::INDEX, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testApplyFiltersOnIndexPage(): void
    {
        $manufacturerA = Manufacturer::factory()->createOne([
            'name' => 'Kuat Drive Yards',
        ]);

        $manufacturerB = Manufacturer::factory()->createOne([
            'name' => 'Incom Corporation',
        ]);

        $uri = route(ManufacturerRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Manufacturers') . '</h1>',
            $response->content()
        );

        $this->assertStringContainsString(
            '<b>' . Str::limit($manufacturerA->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );
        $this->assertStringContainsString(
            '<b>' . Str::limit($manufacturerB->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );

        $uri = route(ManufacturerRouteName::INDEX, [
            'name' => $manufacturerA->name,
            'status' => $manufacturerA->status->value,
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<b>' . Str::limit($manufacturerA->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );
        $this->assertStringNotContainsString(
            '<b>' . Str::limit($manufacturerB->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );

        $uri = route(ManufacturerRouteName::INDEX, [
            'name' => 'Unknown',
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testVisitCreatePage(): void
    {
        $uri = route(ManufacturerRouteName::CREATE);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Create Manufacturer') . '</h1>',
            $response->content()
        );
    }

    public function testVisitEditPage(): void
    {
        $manufacturer = Manufacturer::factory()->createOne([
            'name' => 'Incom Corporation',
        ]);

        $uri = route(ManufacturerRouteName::EDIT, $manufacturer->id);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . $manufacturer->name . '</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCreate(Closure $getRequestData): void
    {
        /** @var Collection<Manufacturer> $manufacturers */
        $manufacturers = Manufacturer::factory()->count(2)->createMany();

        $data = $getRequestData();

        $uri = route(ManufacturerRouteName::STORE);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Manufacturer::class, $manufacturers->count() + 1);

        $manufacturer = Manufacturer::whereName($data['name'])->first();

        $this->assertDataEqualsModel($data, $manufacturer);
    }

    #[DataProvider('requestData')]
    public function testUpdate(Closure $getRequestData): void
    {
        $manufacturer = Manufacturer::factory()->createOne([
            'name' => 'Acme Corporation',
            'status' => Status::DRAFT->value,
            'sort' => $this->faker->randomNumber(3),
        ]);

        $data = $getRequestData();

        $uri = route(ManufacturerRouteName::UPDATE, $manufacturer->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Manufacturer::class, 1);

        $manufacturer->refresh();

        $this->assertDataEqualsModel($data, $manufacturer);
    }

    public function testDelete(): void
    {
        $manufacturer = Manufacturer::factory()->createOne();

        $uri = route(ManufacturerRouteName::DELETE, $manufacturer->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Manufacturer::class, 0);
    }

    public function testTogglePublish(): void
    {
        $manufacturer = Manufacturer::factory()->createOne([
            'status' => Status::DRAFT->value,
        ]);

        $uri = route(ManufacturerRouteName::TOGGLE_PUBLISH, $manufacturer->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $manufacturer->refresh();

        $this->assertEquals(Status::PUBLISHED, $manufacturer->status);

        $uri = route(ManufacturerRouteName::TOGGLE_PUBLISH, $manufacturer->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $manufacturer->refresh();

        $this->assertEquals(Status::DRAFT, $manufacturer->status);
    }

    /**
     * @param array<string, mixed> $data
     * @param Manufacturer $manufacturer
     *
     * @return void
     */
    private function assertDataEqualsModel(array $data, Manufacturer $manufacturer): void
    {
        $this->assertEquals($data['name'], $manufacturer->name);

        if (isset($data['slug'])) {
            $this->assertEquals($data['slug'], $manufacturer->slug);
        }

        $this->assertEquals($data['status'], $manufacturer->status->value);

        if (isset($data['sort'])) {
            $this->assertEquals($data['sort'], $manufacturer->sort);
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

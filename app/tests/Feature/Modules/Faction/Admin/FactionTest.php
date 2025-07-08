<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Faction\Admin;

use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Faction\Admin\Components\Layouts\Index\IndexLayout;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Faction\Common\Models\Faction;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class FactionTest extends AuthorizedAdminUserTestCase
{
    public function testVisitIndexPage(): void
    {
        /** @var Collection<Faction> $models */
        $models = Faction::factory()
            ->createMany([
                [
                    'name' => 'Galactic Republic',
                ],
                [
                    'name' => 'Galactic Empire',
                ],
                [
                    'name' => 'Alliance to Restore the Republic',
                ],
            ]);

        $uri = route(FactionRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Factions') . '</h1>',
            $response->content()
        );

        foreach ($models as $model) {
            $this->assertStringContainsString(
                '<b>' . Str::limit($model->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
                $response->content()
            );
        }

        $uri = route(FactionRouteName::INDEX, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testApplyFiltersOnIndexPage(): void
    {
        $modelA = Faction::factory()->createOne([
            'name' => 'Galactic Empire',
        ]);

        $modelB = Faction::factory()->createOne([
            'name' => 'Alliance to Restore the Republic',
        ]);

        $uri = route(FactionRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Factions') . '</h1>',
            $response->content()
        );

        $this->assertStringContainsString(
            '<b>' . Str::limit($modelA->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );
        $this->assertStringContainsString(
            '<b>' . Str::limit($modelB->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );

        $uri = route(FactionRouteName::INDEX, [
            'name' => $modelA->name,
            'status' => $modelA->status->value,
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<b>' . Str::limit($modelA->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );
        $this->assertStringNotContainsString(
            '<b>' . Str::limit($modelB->name, IndexLayout::NAME_SYMBOL_LIMIT) . '</b>',
            $response->content()
        );

        $uri = route(FactionRouteName::INDEX, [
            'name' => 'Unknown',
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testVisitCreatePage(): void
    {
        $uri = route(FactionRouteName::CREATE);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Create Faction') . '</h1>',
            $response->content()
        );
    }

    public function testVisitEditPage(): void
    {
        $model = Faction::factory()->createOne([
            'name' => 'Alliance to Restore the Republic',
        ]);

        $uri = route(FactionRouteName::EDIT, $model->id);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . $model->name . '</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testUpdate(Closure $getRequestData): void
    {
        $model = Faction::factory()->createOne([
            'name' => 'Alliance to Restore the Empire',
            'status' => Status::DRAFT->value,
            'sort' => $this->faker->randomNumber(3),
        ]);

        $data = $getRequestData();

        $uri = route(FactionRouteName::UPDATE, $model->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Faction::class, 1);

        $model->refresh();

        $this->assertDataEqualsModel($data, $model);
    }

    #[DataProvider('requestData')]
    public function testCreate(Closure $getRequestData): void
    {
        /** @var Collection<Faction> $models */
        $models = Faction::factory()->count(2)->createMany();

        $data = $getRequestData();

        $uri = route(FactionRouteName::STORE);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Faction::class, $models->count() + 1);

        $model = Faction::whereName($data['name'])->first();

        $this->assertDataEqualsModel($data, $model);
    }

    public function testDelete(): void
    {
        $model = Faction::factory()->createOne();

        $uri = route(FactionRouteName::DELETE, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Faction::class, 0);
    }

    public function testTogglePublish(): void
    {
        $model = Faction::factory()->createOne([
            'status' => Status::DRAFT->value,
        ]);

        $uri = route(FactionRouteName::TOGGLE_PUBLISH, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $model->refresh();

        $this->assertEquals(Status::PUBLISHED, $model->status);

        $uri = route(FactionRouteName::TOGGLE_PUBLISH, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $model->refresh();

        $this->assertEquals(Status::DRAFT, $model->status);
    }

    /**
     * @param array<string, mixed> $data
     * @param Faction $model
     *
     * @return void
     */
    private function assertDataEqualsModel(array $data, Faction $model): void
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
                    'name' => 'Alliance to Restore the Republic',
                    'slug' => 'alliance-to-restore-the-republic',
                    'status' => Status::PUBLISHED->value,
                    'sort' => 100,
                ],
            ],
            'required fields only' => [
                static fn (): array => [
                    'name' => 'Alliance to Restore the Republic',
                    'status' => Status::PUBLISHED->value,
                ],
            ],
        ];
    }
}

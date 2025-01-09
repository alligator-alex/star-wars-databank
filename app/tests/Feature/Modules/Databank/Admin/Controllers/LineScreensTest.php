<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Databank\Admin\Controllers;

use App\Modules\Databank\Admin\Components\Line\Layouts\List\ListTable;
use App\Modules\Databank\Admin\Enums\LineRouteName;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Models\Line;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class LineScreensTest extends AuthorizedAdminUserTestCase
{
    public function testCanVisitListPage(): void
    {
        /** @var Collection<Line> $models */
        $models = Line::factory()
            ->createMany([
                [
                    'name' => 'TIE',
                ],
                [
                    'name' => 'X-Wing',
                ],
                [
                    'name' => 'Y-Wing',
                ],
            ]);

        $uri = route(LineRouteName::LIST);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Lines') . '</h1>',
            $response->content()
        );

        foreach ($models as $model) {
            $this->assertStringContainsString(
                '<b>' . Str::limit($model->name, ListTable::NAME_SYMBOL_LIMIT) . '</b>',
                $response->content()
            );
        }

        $uri = route(LineRouteName::LIST, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testCanApplyFiltersOnListPage(): void
    {
        $modelA = Line::factory()->createOne([
            'name' => 'TIE',
        ]);

        $modelB = Line::factory()->createOne([
            'name' => 'X-Wing',
        ]);

        $uri = route(LineRouteName::LIST);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Lines') . '</h1>',
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

        $uri = route(LineRouteName::LIST, [
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

        $uri = route(LineRouteName::LIST, [
            'name' => 'Unknown',
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testCanVisitOnePage(): void
    {
        $model = Line::factory()->createOne([
            'name' => 'X-Wing',
        ]);

        $uri = route(LineRouteName::ONE, $model->id);
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
        $model = Line::factory()->createOne([
            'name' => 'Y-Wing',
            'status' => Status::DRAFT->value,
            'sort' => $this->faker->randomNumber(3),
        ]);

        $data = $getRequestData();

        $uri = route(LineRouteName::UPDATE, $model->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Line::class, 1);

        $model->refresh();

        $this->assertDataEqualsModel($data, $model);
    }

    public function testCanVisitNewPage(): void
    {
        $uri = route(LineRouteName::NEW);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Create new Line') . '</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCanCreate(Closure $getRequestData): void
    {
        /** @var Collection<Line> $models */
        $models = Line::factory()->count(2)->createMany();

        $data = $getRequestData();

        $uri = route(LineRouteName::CREATE);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Line::class, $models->count() + 1);

        $model = Line::whereName($data['name'])->first();

        $this->assertDataEqualsModel($data, $model);
    }

    public function testCanDelete(): void
    {
        $model = Line::factory()->createOne();

        $uri = route(LineRouteName::DELETE, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Line::class, 0);
    }

    public function testCanTogglePublish(): void
    {
        $model = Line::factory()->createOne([
            'status' => Status::DRAFT->value,
        ]);

        $uri = route(LineRouteName::TOGGLE_PUBLISH, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $model->refresh();

        $this->assertEquals(Status::PUBLISHED, $model->status);

        $uri = route(LineRouteName::TOGGLE_PUBLISH, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $model->refresh();

        $this->assertEquals(Status::DRAFT, $model->status);
    }

    /**
     * @param array<string, mixed> $data
     * @param Line $model
     *
     * @return void
     */
    private function assertDataEqualsModel(array $data, Line $model): void
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
                    'name' => 'X-Wing',
                    'slug' => 'x-wing',
                    'status' => Status::PUBLISHED->value,
                    'sort' => 100,
                ],
            ],
            'required fields only' => [
                static fn (): array => [
                    'name' => 'X-Wing',
                    'status' => Status::PUBLISHED->value,
                ],
            ],
        ];
    }
}

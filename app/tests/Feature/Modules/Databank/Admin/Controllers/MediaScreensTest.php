<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Databank\Admin\Controllers;

use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Databank\Admin\Components\Media\Layouts\List\ListTable;
use App\Modules\Databank\Admin\Enums\MediaRouteName;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Enums\MediaType;
use App\Modules\Databank\Common\Models\Media;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class MediaScreensTest extends AuthorizedAdminUserTestCase
{
    public function testCanVisitListPage(): void
    {
        /** @var Collection<Media> $models */
        $models = Media::factory()
            ->createMany([
                [
                    'name' => 'Star Wars: Episode IV A New Hope',
                ],
                [
                    'name' => 'Star Wars: Battlefront',
                ],
                [
                    'name' => 'Star Wars: Episode III Revenge of the Sith',
                ],
            ]);

        $uri = route(MediaRouteName::LIST);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Media') . '</h1>',
            $response->content()
        );

        foreach ($models as $model) {
            $this->assertStringContainsString(
                '<b>' . Str::limit($model->name, ListTable::NAME_SYMBOL_LIMIT) . '</b>',
                $response->content()
            );
        }

        $uri = route(MediaRouteName::LIST, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testCanApplyFiltersOnListPage(): void
    {
        $modelA = Media::factory()->createOne([
            'name' => 'Star Wars: Episode IV A New Hope',
        ]);

        $modelB = Media::factory()->createOne([
            'name' => 'Star Wars: Episode III Revenge of the Sith',
        ]);

        $uri = route(MediaRouteName::LIST);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Media') . '</h1>',
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

        $uri = route(MediaRouteName::LIST, [
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

        $uri = route(MediaRouteName::LIST, [
            'name' => 'Unknown',
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testCanVisitOnePage(): void
    {
        $model = Media::factory()->createOne([
            'name' => 'Star Wars: Episode IV A New Hope',
        ]);

        $uri = route(MediaRouteName::ONE, $model->id);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . $model->name . ' (' . $model->releaseYear() . ')</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCanUpdate(Closure $getRequestData): void
    {
        $model = Media::factory()->createOne([
            'name' => 'Swamp Wars: Episode CCXLVI Revenge of the Shrek',
            'status' => Status::DRAFT->value,
            'sort' => $this->faker->randomNumber(3),
            'type' => MediaType::SERIES->value,
            'release_date' => '2977-05-27',
            'poster_id' => Attachment::factory()->createOne()->id,
        ]);

        $data = $getRequestData();

        $uri = route(MediaRouteName::UPDATE, $model->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Media::class, 1);

        $model->refresh();

        $this->assertDataEqualsModel($data, $model);
    }

    public function testCanVisitNewPage(): void
    {
        $uri = route(MediaRouteName::NEW);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Create new Media') . '</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCanCreate(Closure $getRequestData): void
    {
        /** @var Collection<Media> $models */
        $models = Media::factory()->count(2)->createMany();

        $data = $getRequestData();

        $uri = route(MediaRouteName::CREATE);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Media::class, $models->count() + 1);

        $model = Media::whereName($data['name'])->first();

        $this->assertDataEqualsModel($data, $model);
    }

    public function testCanDelete(): void
    {
        $model = Media::factory()->createOne();

        $uri = route(MediaRouteName::DELETE, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Media::class, 0);
    }

    public function testCanTogglePublish(): void
    {
        $model = Media::factory()->createOne([
            'status' => Status::DRAFT->value,
        ]);

        $uri = route(MediaRouteName::TOGGLE_PUBLISH, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $model->refresh();

        $this->assertEquals(Status::PUBLISHED, $model->status);

        $uri = route(MediaRouteName::TOGGLE_PUBLISH, $model->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $model->refresh();

        $this->assertEquals(Status::DRAFT, $model->status);
    }

    /**
     * @param array<string, mixed> $data
     * @param Media $model
     *
     * @return void
     */
    private function assertDataEqualsModel(array $data, Media $model): void
    {
        $this->assertEquals($data['name'], $model->name);

        if (isset($data['slug'])) {
            $this->assertEquals($data['slug'], $model->slug);
        }

        $this->assertEquals($data['status'], $model->status->value);

        if (isset($data['sort'])) {
            $this->assertEquals($data['sort'], $model->sort);
        }

        $this->assertEquals($data['type'], $model->type->value);
        $this->assertEquals($data['releaseDate'], $model->release_date->format('Y-m-d'));
        $this->assertEquals($data['posterId'], $model->poster_id);
    }

    /**
     * @return array<string, mixed>
     */
    public static function requestData(): array
    {
        return [
            'all fields' => [
                static fn (): array => [
                    'name' => 'Star Wars: Episode III Revenge of the Sith',
                    'slug' => 'star-wars-episode-iii-revenge-of-the-sith',
                    'status' => Status::PUBLISHED->value,
                    'sort' => 100,
                    'type' => MediaType::MOVIE->value,
                    'releaseDate' => '2005-05-19',
                    'posterId' => Attachment::factory()->createOne()->id,
                ],
            ],
            'required field only' => [
                static fn (): array => [
                    'name' => 'Star Wars: Episode III Revenge of the Sith',
                    'status' => Status::PUBLISHED->value,
                    'type' => MediaType::MOVIE->value,
                    'releaseDate' => '2005-05-19',
                    'posterId' => Attachment::factory()->createOne()->id,
                ],
            ],
        ];
    }
}

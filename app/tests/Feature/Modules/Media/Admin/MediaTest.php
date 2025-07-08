<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Media\Admin;

use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Media\Admin\Enums\MediaRouteName;
use App\Modules\Media\Common\Enums\MediaType;
use App\Modules\Media\Common\Models\Media;
use Closure;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class MediaTest extends AuthorizedAdminUserTestCase
{
    public function testVisitIndexPage(): void
    {
        /** @var Collection<Media> $medias */
        $medias = Media::factory()
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

        $uri = route(MediaRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Media') . '</h1>',
            $response->content()
        );

        foreach ($medias as $media) {
            $this->assertStringContainsString(
                '<b>' . $media->name . '</b>',
                $response->content()
            );
        }

        $uri = route(MediaRouteName::INDEX, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testApplyFiltersOnIndexPage(): void
    {
        $mediaA = Media::factory()->createOne([
            'name' => 'Star Wars: Episode IV A New Hope',
        ]);

        $mediaB = Media::factory()->createOne([
            'name' => 'Star Wars: Episode III Revenge of the Sith',
        ]);

        $uri = route(MediaRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Media') . '</h1>',
            $response->content()
        );
        $this->assertStringContainsString(
            '<b>' . $mediaA->name . '</b>',
            $response->content()
        );
        $this->assertStringContainsString(
            '<b>' . $mediaB->name . '</b>',
            $response->content()
        );

        $uri = route(MediaRouteName::INDEX, [
            'name' => $mediaA->name,
            'status' => $mediaA->status->value,
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<b>' . $mediaA->name . '</b>',
            $response->content()
        );
        $this->assertStringNotContainsString(
            '<b>' . $mediaB->name . '</b>',
            $response->content()
        );

        $uri = route(MediaRouteName::INDEX, [
            'name' => 'Unknown',
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testVisitCreatePage(): void
    {
        $uri = route(MediaRouteName::CREATE);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Create Media') . '</h1>',
            $response->content()
        );
    }

    public function testVisitEditPage(): void
    {
        $media = Media::factory()->createOne([
            'name' => 'Star Wars: Episode IV A New Hope',
        ]);

        $uri = route(MediaRouteName::EDIT, $media->id);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . $media->name . ' (' . $media->releaseYear() . ')</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCreate(Closure $getRequestData): void
    {
        /** @var Collection<Media> $medias */
        $medias = Media::factory()->count(2)->createMany();

        $data = $getRequestData();

        $uri = route(MediaRouteName::STORE);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Media::class, $medias->count() + 1);

        $media = Media::whereName($data['name'])->first();

        $this->assertDataEqualsModel($data, $media);
    }

    #[DataProvider('requestData')]
    public function testUpdate(Closure $getRequestData): void
    {
        $media = Media::factory()->createOne([
            'name' => 'Swamp Wars: Episode CCXLVI Revenge of the Shrek',
            'status' => Status::DRAFT->value,
            'sort' => $this->faker->randomNumber(3),
            'type' => MediaType::SERIES->value,
            'release_date' => '2977-05-27',
            'poster_id' => Attachment::factory()->createOne()->id,
        ]);

        $data = $getRequestData();

        $uri = route(MediaRouteName::UPDATE, $media->id);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Media::class, 1);

        $media->refresh();

        $this->assertDataEqualsModel($data, $media);
    }

    public function testDelete(): void
    {
        $media = Media::factory()->createOne();

        $uri = route(MediaRouteName::DELETE, $media->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseCount(Media::class, 0);
    }

    public function testTogglePublish(): void
    {
        $media = Media::factory()->createOne([
            'status' => Status::DRAFT->value,
        ]);

        $uri = route(MediaRouteName::TOGGLE_PUBLISH, $media->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $media->refresh();

        $this->assertEquals(Status::PUBLISHED, $media->status);

        $uri = route(MediaRouteName::TOGGLE_PUBLISH, $media->id);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $media->refresh();

        $this->assertEquals(Status::DRAFT, $media->status);
    }

    /**
     * @param array<string, mixed> $data
     * @param Media $media
     *
     * @return void
     */
    private function assertDataEqualsModel(array $data, Media $media): void
    {
        $this->assertEquals($data['name'], $media->name);

        if (isset($data['slug'])) {
            $this->assertEquals($data['slug'], $media->slug);
        }

        $this->assertEquals($data['status'], $media->status->value);

        if (isset($data['sort'])) {
            $this->assertEquals($data['sort'], $media->sort);
        }

        $this->assertEquals($data['type'], $media->type->value);
        $this->assertEquals($data['releaseDate'], $media->release_date->format('Y-m-d'));
        $this->assertEquals($data['posterId'], $media->poster_id);
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

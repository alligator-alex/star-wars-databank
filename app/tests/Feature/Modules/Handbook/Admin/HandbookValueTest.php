<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Handbook\Admin;

use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Models\HandbookValue;
use Closure;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\AuthorizedAdminUserTestCase;

class HandbookValueTest extends AuthorizedAdminUserTestCase
{
    public function testVisitIndexPage(): void
    {
        /** @var Collection<HandbookValue> $handbookValues */
        $handbookValues = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_LINE)
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

        /** @var HandbookValue $handbookValue */
        $handbookValue = $handbookValues->first();

        $uri = route(HandbookValueRouteName::INDEX, [
            'handbookId' => $handbookValue->handbook_id,
            'handbookValueId' => $handbookValue->id,
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Vehicle Lines') . '</h1>',
            $response->content()
        );

        foreach ($handbookValues as $handbookValue) {
            $this->assertStringContainsString(
                '<b>' . $handbookValue->name . '</b>',
                $response->content()
            );
        }

        $uri = route(HandbookValueRouteName::INDEX, [
            'handbookId' => $handbookValue->handbook_id,
            'handbookValueId' => $handbookValue->id,
            'page' => 2,
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testApplyFiltersOnIndexPage(): void
    {
        $handbookValueA = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_LINE)
            ->createOne([
                'name' => 'TIE',
            ]);

        $handbookValueB = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_LINE)
            ->createOne([
                'name' => 'X-Wing',
            ]);

        $uri = route(HandbookValueRouteName::INDEX, [
            'handbookId' => $handbookValueA->handbook_id,
            'handbookValueId' => $handbookValueA->id,
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Vehicle Lines') . '</h1>',
            $response->content()
        );

        $this->assertStringContainsString(
            '<b>' . $handbookValueA->name . '</b>',
            $response->content()
        );
        $this->assertStringContainsString(
            '<b>' . $handbookValueB->name . '</b>',
            $response->content()
        );

        $uri = route(HandbookValueRouteName::INDEX, [
            'handbookId' => $handbookValueA->handbook_id,
            'handbookValueId' => $handbookValueA->id,
            'name' => $handbookValueA->name,
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<b>' . $handbookValueA->name . '</b>',
            $response->content()
        );
        $this->assertStringNotContainsString(
            '<b>' . $handbookValueB->name . '</b>',
            $response->content()
        );

        $uri = route(HandbookValueRouteName::INDEX, [
            'handbookId' => $handbookValueA->handbook_id,
            'handbookValueId' => $handbookValueA->id,
            'name' => 'Unknown',
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('No results found for your current filters'), $response->content());
    }

    public function testVisitCreatePage(): void
    {
        $handbook = Handbook::whereType(HandbookType::VEHICLE_CATEGORY)->first();

        $uri = route(HandbookValueRouteName::CREATE, [
            'handbookId' => $handbook->id,
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . __('Create Vehicle Category') . '</h1>',
            $response->content()
        );
    }

    public function testVisitEditPage(): void
    {
        $handbookValue = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_LINE)
            ->createOne([
            'name' => 'X-Wing',
        ]);

        $uri = route(HandbookValueRouteName::EDIT, [
            'handbookId' => $handbookValue->handbook_id,
            'handbookValueId' => $handbookValue->id,
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . $handbookValue->name . '</h1>',
            $response->content()
        );
    }

    #[DataProvider('requestData')]
    public function testCreate(Closure $getRequestData): void
    {
        /** @var Collection<HandbookValue> $handbookValues */
        $handbookValues = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_LINE)
            ->count(2)
            ->createMany();

        /** @var HandbookValue $handbookValue */
        $handbookValue = $handbookValues->first();

        $data = $getRequestData();

        $uri = route(HandbookValueRouteName::STORE, [
            'handbookId' => $handbookValue->handbook_id,
            'handbookValueId' => $handbookValue->id,
        ]);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertCount($handbookValues->count() + 1, HandbookValue::whereHandbookId($handbookValue->handbook_id)->get());

        $handbookValue = HandbookValue::whereHandbookId($handbookValue->handbook_id)
            ->where('name', '=', $data['name'])
            ->first();

        $this->assertDataEqualsModel($data, $handbookValue);
    }

    #[DataProvider('requestData')]
    public function testUpdate(Closure $getRequestData): void
    {
        $handbookValue = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_LINE)
            ->createOne([
                'name' => 'Y-Wing',
            ]);

        $data = $getRequestData();

        $uri = route(HandbookValueRouteName::UPDATE, [
            'handbookId' => $handbookValue->handbook_id,
            'handbookValueId' => $handbookValue->id,
        ]);
        $this->post($uri, $data)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertCount(1, HandbookValue::whereHandbookId($handbookValue->handbook_id)->get());

        $handbookValue->refresh();

        $this->assertDataEqualsModel($data, $handbookValue);
    }

    public function testDelete(): void
    {
        $handbookValue = HandbookValue::factory()
            ->ofHandbookType(HandbookType::VEHICLE_LINE)
            ->createOne();

        $uri = route(HandbookValueRouteName::DELETE, [
            'handbookId' => $handbookValue->handbook_id,
            'handbookValueId' => $handbookValue->id,
        ]);
        $this->post($uri)
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertCount(0, HandbookValue::whereHandbookId($handbookValue->handbook_id)->get());
    }

    /**
     * @param array<string, mixed> $data
     * @param HandbookValue $handbookValue
     *
     * @return void
     */
    private function assertDataEqualsModel(array $data, HandbookValue $handbookValue): void
    {
        $this->assertEquals($data['name'], $handbookValue->name);

        if (isset($data['slug'])) {
            $this->assertEquals($data['slug'], $handbookValue->slug);
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
                ],
            ],
            'required fields only' => [
                static fn (): array => [
                    'name' => 'X-Wing',
                ],
            ],
        ];
    }
}

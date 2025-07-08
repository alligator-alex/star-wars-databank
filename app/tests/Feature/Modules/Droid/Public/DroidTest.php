<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Droid\Public;

use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Media\Common\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DroidTest extends TestCase
{
    public function testVisitIndexPage(): void
    {
        $droids = Droid::factory()
            ->count(5)
            ->createMany();

        $uri = route(DroidRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        /** @var Droid $droid */
        $droid = $droids->random()->first();

        $this->assertStringContainsString(
            '<a href="' . route(DroidRouteName::DETAIL, $droid->slug, false) . '"',
            $response->content()
        );

        // test pagination
        $uri = route(DroidRouteName::INDEX, ['page' => 2]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('Nothing found'), $response->content());

        // test json response
        $uri = route(DroidRouteName::INDEX);
        $response = $this->get($uri, ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(Response::HTTP_OK);

        $this->assertJson($response->content());
    }

    public function testApplyFiltersOnIndexPage(): void
    {
        $lineA = HandbookValue::factory()
            ->ofHandbookType(HandbookType::DROID_LINE)
            ->createOne([
                'name' => 'B1 Battle Droid',
                'slug' => 'b1-battle-droid',
            ]);

        $lineB = HandbookValue::factory()
            ->ofHandbookType(HandbookType::DROID_LINE)
            ->createOne([
                'name' => 'B2 Super Battle Droid',
                'slug' => 'b2-super-battle-droid',
            ]);

        $lineC = HandbookValue::factory()
            ->ofHandbookType(HandbookType::DROID_LINE)
            ->createOne([
                'name' => 'R',
                'slug' => 'r',
            ]);

        $droidA = Droid::factory()->createOne([
            'name' => 'B1-series battle droid',
            'line_id' => $lineA->id,
        ]);

        $droidA->manufacturers()->attach(Manufacturer::factory()->createOne());
        $droidA->factions()->attach(Faction::factory()->createOne());
        $droidA->appearances()->attach(Media::factory()->createOne());

        $droidB = Droid::factory()->createOne([
            'name' => 'B2-series super battle droid',
            'line_id' => $lineB->id,
        ]);

        $uri = route(DroidRouteName::INDEX);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<a href="' . route(DroidRouteName::DETAIL, $droidA->slug, false) . '"',
            $response->content()
        );
        $this->assertStringContainsString(
            '<a href="' . route(DroidRouteName::DETAIL, $droidB->slug, false) . '"',
            $response->content()
        );

        $uri = route(DroidRouteName::INDEX, [
            'lines' => [$droidA->line->slug],
            'models' => [$droidA->model->slug],
            'classes' => [$droidA->class->slug],
            'manufacturers' => [$droidA->manufacturers->first()->slug],
            'factions' => [$droidA->factions->first()->slug],
            'media' => [$droidA->appearances->first()->slug],
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<a href="' . route(DroidRouteName::DETAIL, $droidA->slug, false) . '"',
            $response->content()
        );
        $this->assertStringNotContainsString(
            '<a href="' . route(DroidRouteName::DETAIL, $droidB->slug, false) . '"',
            $response->content()
        );

        $uri = route(DroidRouteName::INDEX, [
            'lines' => [$lineC->slug]
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(__('Nothing found'), $response->content());
    }

    public function testVisitDetailPage(): void
    {
        $droid = Droid::factory()->createOne();

        $uri = route(DroidRouteName::DETAIL, $droid->slug);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString("<h1>{$droid->name}</h1>", $response->content());

        $uri = route(DroidRouteName::DETAIL, 'some-non-existing-slug');
        $this->get($uri)->assertStatus(Response::HTTP_NOT_FOUND);
    }
}

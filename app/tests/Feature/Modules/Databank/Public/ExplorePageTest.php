<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Databank\Public;

use App\Modules\Databank\Public\Enums\DatabankRouteName;
use App\Modules\Databank\Public\Enums\ExploreRootType;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Media\Common\Models\Media;
use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ExplorePageTest extends TestCase
{
    #[DataProvider('exploreData')]
    public function testVisitExplorePage(Closure $getExploreData): void
    {
        $data = $getExploreData();

        $uri = route(DatabankRouteName::EXPLORE, [
            'type' => $data['type'],
            'slug' => $data['slug'],
        ]);
        $response = $this->get($uri)
            ->assertStatus(Response::HTTP_OK);

        $this->assertStringContainsString(
            '<h1 class="' . self::HEADING_CLASS_NAME . '">' . $data['name'] . '</h1>',
            $response->content()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function exploreData(): array
    {
        return [
            'faction' => [
                static function (): array {
                    $faction = Faction::factory()->createOne([
                        'name' => 'Garactic Republic',
                    ]);

                    return [
                        'type' => ExploreRootType::FACTION->value,
                        'slug' => $faction->slug,
                        'name' => $faction->name,
                    ];
                }
            ],
            'media' => [
                static function (): array {
                    $media = Media::factory()->createOne([
                        'name' => 'Andor',
                    ]);

                    return [
                        'type' => ExploreRootType::MEDIA->value,
                        'slug' => $media->slug,
                        'name' => $media->name,
                    ];
                }
            ],
        ];
    }
}

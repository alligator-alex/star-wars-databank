<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Databank\Import;

use App\Modules\Databank\Common\Models\Faction;
use App\Modules\Databank\Common\Models\Manufacturer;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Models\Media;
use App\Modules\Databank\Common\Repositories\FactionRepository;
use App\Modules\Databank\Common\Repositories\LineRepository;
use App\Modules\Databank\Common\Repositories\ManufacturerRepository;
use App\Modules\Databank\Common\Repositories\VehicleRepository;
use App\Modules\Databank\Common\Repositories\MediaRepository;
use App\Modules\Databank\Import\Contracts\Importer;
use App\Modules\Databank\Import\Contracts\Parser;
use App\Modules\Databank\Import\Importer\WookieepediaImporter;
use App\Modules\Databank\Import\Parser\WookiepediaParser;
use Closure;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;
use Tests\TestCase;

class ImporterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $dummyLogger = new class () implements LoggerInterface {
            use LoggerTrait;

            public function log($level, Stringable|string $message, array $context = []): void
            {
            }
        };

        $this->app->bind(Parser::class, static fn (): Parser => new WookiepediaParser($dummyLogger));

        $this->app->bind(Importer::class, static fn (): Importer => new WookieepediaImporter(
            $dummyLogger,
            new VehicleRepository(),
            new LineRepository(),
            new ManufacturerRepository(),
            new FactionRepository(),
            new MediaRepository(),
        ));
    }

    #[DataProvider('messages')]
    public function testCanImport(Closure $getMessages): void
    {
        /** @var Parser $importer */
        $parser = app(Parser::class);

        /** @var Importer $importer */
        $importer = app(Importer::class);

        $importer->import($parser->parse($getMessages()));

        $this->assertEquals(2, Vehicle::query()->withDrafts()->count());
        $this->assertEquals(2, Manufacturer::query()->count());
        $this->assertEquals(4, Faction::query()->count());
        $this->assertEquals(3, Media::query()->count());
    }

    /**
     * @return array<string, mixed>
     */
    public static function messages(): array
    {
        return [
            'multiple vehicles' => [
                static function (): Generator {
                    $messages = [
                        [
                            'name' => 'T-65B X-wing starfighter',
                            'category' => 'Starship',
                            'line' => 'X-wing',
                            'type' => 'Starfighter',
                            'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                            'description' => 'The T-65B X-wing starfighter, also known as the T-65B space superiority fighter, or T-65B X-wing multi-role starfighter, was a single-seat craft manufactured by Incom Corporation and used most famously by the Alliance to Restore the Republic during the Galactic Civil War. Renowned for its speed and maneuverability in battle, it became the backbone of the Rebel Alliance Starfighter Corps, being both harder hitting and tougher under fire than its main adversary, the mass-produced TIE/ln space superiority starfighter.',
                            'url' => 'https://starwars.fandom.com/wiki/T-65B_X-wing_starfighter',
                            'relatedUrl' => 'https://starwars.fandom.com/wiki/T-65B_X-wing_starfighter/Legends',
                            'manufacturers' => [
                                [
                                    'name' => 'Incom Corporation',
                                    'note' => null,
                                    'children' => [],
                                ],
                            ],
                            'factions' => [
                                [
                                    'name' => 'Alliance to Restore the Republic',
                                    'note' => null,
                                    'children' => [
                                        // should not be imported
                                        [
                                            'name' => 'Rebel Alliance Navy',
                                            'note' => null,
                                        ],
                                        // should not be imported
                                        [
                                            'name' => 'Rogue Squadron',
                                            'note' => null,
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'Jedi Order',
                                    'note' => null,
                                    'children' => [],
                                ],
                                [
                                    'name' => 'New Republic',
                                    'note' => null,
                                    'children' => [
                                        [
                                            'name' => 'New Republic Defense Fleet',
                                            'note' => null,
                                        ]
                                    ],
                                ],
                                // should not be imported
                                [
                                    'name' => 'Partisans',
                                    'note' => null,
                                    'children' => [],
                                ],
                                // should not be imported
                                [
                                    'name' => 'Amaxine warriors',
                                    'note' => null,
                                    'children' => [],
                                ],
                            ],
                            'technicalSpecifications' => [
                                [
                                    'name' => 'Length',
                                    'value' => '12.5 meters',
                                ],
                                [
                                    'name' => 'Width',
                                    'value' => '11 meters',
                                ],
                                [
                                    'name' => 'Height',
                                    'value' => '4.4 meters',
                                ],
                            ],
                            'isCanon' => true,
                            'appearances' => [
                                [
                                    'name' => 'Star Wars Battlefront',
                                    'releaseDate' => 'November 17, 2015',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                    'type' => 'Game',
                                ],
                                [
                                    'name' => 'Star Wars: Episode V The Empire Strikes Back',
                                    'releaseDate' => 'May 21, 1980',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                    'type' => 'Movie',
                                ],
                                [
                                    'name' => 'Star Wars: Episode IV A New Hope',
                                    'releaseDate' => 'May 25, 1977',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                    'type' => 'Movie',
                                ],
                                // should not be imported
                                [
                                    'name' => 'Lost Stars (webcomic)',
                                    'releaseDate' => 'May 4, 2017',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=200',
                                    'type' => 'Comic Book',
                                ],
                                // should not be imported
                                [
                                    'name' => 'Rogue One - Cassian & K-2SO Special 1',
                                    'releaseDate' => 'August 9, 2017',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=203',
                                    'type' => 'Comic Book',
                                ],
                            ],
                        ],
                        [
                            'name' => 'TIE/ln space superiority starfighter',
                            'category' => 'Starship',
                            'line' => 'TIE',
                            'type' => 'Starfighter',
                            'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                            'description' => 'The TIE/ln space superiority starfighter, also known as the TIE/LN starfighter or TIE/ln starfighter and commonly called the TIE fighter or simply the TIE/ln, was the signature starfighter of the Galactic Empire and symbol of its space superiority.',
                            'url' => 'https://starwars.fandom.com/wiki/TIE/ln_space_superiority_starfighter',
                            'relatedUrl' => 'https://starwars.fandom.com/wiki/TIE/LN_starfighter',
                            'manufacturers' => [
                                [
                                    'name' => 'Sienar Fleet Systems',
                                    'note' => null,
                                    'children' => [],
                                ],
                            ],
                            'factions' => [
                                [
                                    'name' => 'Galactic Empire',
                                    'note' => null,
                                    'children' => [
                                        // should not be imported
                                        [
                                            'name' => 'Army Air Corps',
                                            'note' => null,
                                        ],
                                        // should not be imported
                                        [
                                            'name' => 'Imperial Starfighter Corps',
                                            'note' => null,
                                        ],
                                    ],
                                ],
                                // should not be imported
                                [
                                    'name' => 'Alliance to Restore the Republic',
                                    'note' => 'stolen',
                                    'children' => [],
                                ],
                                // should not be imported
                                [
                                    'name' => 'Imperial Remnants',
                                    'note' => null,
                                    'children' => [],
                                ],
                            ],
                            'technicalSpecifications' => [
                                [
                                    'name' => 'Length',
                                    'value' => '7.24 meters',
                                ],
                                [
                                    'name' => 'Width',
                                    'value' => '6.7 meters',
                                ],
                                [
                                    'name' => 'Height',
                                    'value' => '8.82 meters',
                                ],
                            ],
                            'isCanon' => true,
                            'appearances' => [
                                [
                                    'name' => 'Star Wars Battlefront',
                                    'releaseDate' => 'November 17, 2015',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                    'type' => 'Movie',
                                ],
                                [
                                    'name' => 'Star Wars: Episode V The Empire Strikes Back',
                                    'releaseDate' => 'May 21, 1980',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                    'type' => 'Movie',
                                ],
                                [
                                    'name' => 'Star Wars: Episode IV A New Hope',
                                    'releaseDate' => 'May 25, 1977',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                    'type' => 'Movie',
                                ],
                                // should not be imported
                                [
                                    'name' => 'Star Wars: Force Arena',
                                    'releaseDate' => 'March 18, 2019',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                    'type' => 'Game',
                                ],
                                // should not be imported
                                [
                                    'name' => 'Trapped in the Death Star!',
                                    'releaseDate' => 'November 1, 2016',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                    'type' => 'Book',
                                ],
                            ],
                        ],
                    ];

                    foreach ($messages as $message) {
                        yield json_encode($message, JSON_THROW_ON_ERROR);
                    }
                },
            ],
        ];
    }
}

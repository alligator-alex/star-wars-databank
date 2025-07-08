<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Databank\Import;

use App\Modules\Databank\Import\Contracts\Importer;
use App\Modules\Databank\Import\Contracts\Parser;
use App\Modules\Databank\Import\Importer\WookieepediaImporter;
use App\Modules\Databank\Import\Parser\WookiepediaParser;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Manufacturer\Common\Repositories\ManufacturerRepository;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Closure;
use Generator;
use Illuminate\Support\Facades\DB;
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

        DB::table(HandbookValue::tableName())->truncate();

        $dummyLogger = new class () implements LoggerInterface {
            use LoggerTrait;

            public function log($level, Stringable|string $message, array $context = []): void
            {
            }
        };

        $this->app->bind(Parser::class, static fn (): Parser => new WookiepediaParser($dummyLogger));

        $this->app->bind(Importer::class, static fn (): Importer => new WookieepediaImporter(
            $dummyLogger,
            new FactionRepository(),
            new ManufacturerRepository(),
            new MediaRepository(),
            new HandbookRepository(),
            new HandbookValueRepository()
        ));
    }

    #[DataProvider('messages')]
    public function testImport(Closure $getMessages): void
    {
        /** @var Parser $importer */
        $parser = app(Parser::class);

        /** @var Importer $importer */
        $importer = app(Importer::class);

        /** @var Generator $messages */
        $messages = $getMessages();
        $firstMessage = json_decode($messages->current(), true);

        $importer->import($parser->parse($messages));

        match ($firstMessage['entityType']) {
            'Vehicle' => $this->checkImportedVehicles(),
            'Droid' => $this->checkImportedDroids(),
        };
    }

    private function checkImportedVehicles(): void
    {
        $categoryHandbook = Handbook::whereType(HandbookType::VEHICLE_CATEGORY->value)->first();
        $lineHandbook = Handbook::whereType(HandbookType::VEHICLE_LINE->value)->first();
        $typeHandbook = Handbook::whereType(HandbookType::VEHICLE_TYPE->value)->first();

        $this->assertEquals(2, Vehicle::query()->withDrafts()->count());

        $this->assertEquals(1, HandbookValue::whereHandbookId($categoryHandbook->id)->count());
        $this->assertEquals(2, HandbookValue::whereHandbookId($lineHandbook->id)->count());
        $this->assertEquals(1, HandbookValue::whereHandbookId($typeHandbook->id)->count());

        $this->assertEquals(2, Manufacturer::query()->count());
        $this->assertEquals(4, Faction::query()->count());
        $this->assertEquals(3, Media::query()->count());
    }

    private function checkImportedDroids(): void
    {
        $modelHandbook = Handbook::whereType(HandbookType::DROID_MODEL->value)->first();
        $classHandbook = Handbook::whereType(HandbookType::DROID_CLASS->value)->first();

        $this->assertEquals(1, Droid::query()->withDrafts()->count());

        $this->assertEquals(1, HandbookValue::whereHandbookId($modelHandbook->id)->count());
        $this->assertEquals(1, HandbookValue::whereHandbookId($classHandbook->id)->count());

        $this->assertEquals(1, Manufacturer::query()->count());
        $this->assertEquals(3, Faction::query()->count());
        $this->assertEquals(3, Media::query()->count());
    }

    /**
     * @return array<string, mixed>
     */
    public static function messages(): array
    {
        return [
            'vehicles' => [
                static function (): Generator {
                    $messages = [
                        [
                            'entityType' => 'Vehicle',
                            'mainInfo' => [
                                'name' => 'T-65B X-wing starfighter',
                                'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                'description' => 'The T-65B X-wing starfighter, also known as the T-65B space superiority fighter, or T-65B X-wing multi-role starfighter, was a single-seat craft manufactured by Incom Corporation and used most famously by the Alliance to Restore the Republic during the Galactic Civil War. Renowned for its speed and maneuverability in battle, it became the backbone of the Rebel Alliance Starfighter Corps, being both harder hitting and tougher under fire than its main adversary, the mass-produced TIE/ln space superiority starfighter.',
                                'url' => 'https://starwars.fandom.com/wiki/T-65B_X-wing_starfighter',
                                'relatedUrl' => 'https://starwars.fandom.com/wiki/T-65B_X-wing_starfighter/Legends',
                                'isCanon' => true,
                            ],
                            'category' => 'Starship',
                            'line' => 'X-wing',
                            'type' => 'Starfighter',
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
                                    'type' => 'ComicBook',
                                ],
                                // should not be imported
                                [
                                    'name' => 'Rogue One - Cassian & K-2SO Special 1',
                                    'releaseDate' => 'August 9, 2017',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=203',
                                    'type' => 'ComicBook',
                                ],
                            ],
                        ],
                        [
                            'entityType' => 'Vehicle',
                            'mainInfo' => [
                                'name' => 'TIE/ln space superiority starfighter',
                                'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                'description' => 'The TIE/ln space superiority starfighter, also known as the TIE/LN starfighter or TIE/ln starfighter and commonly called the TIE fighter or simply the TIE/ln, was the signature starfighter of the Galactic Empire and symbol of its space superiority.',
                                'url' => 'https://starwars.fandom.com/wiki/TIE/ln_space_superiority_starfighter',
                                'relatedUrl' => 'https://starwars.fandom.com/wiki/TIE/LN_starfighter',
                                'isCanon' => true,
                            ],
                            'category' => 'Starship',
                            'line' => 'TIE',
                            'type' => 'Starfighter',
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

            'droids' => [
                static function (): Generator {
                    $messages = [
                        [
                            'entityType' => 'Droid',
                            'mainInfo' => [
                                'name' => 'C-3PO',
                                'imageUrl' => 'https://images.placeholders.dev/?width=20&height=20',
                                'description' => 'C-3PO (See-Threepio) was a 3PO-series protocol droid designed to interact with organics, programmed primarily for etiquette and protocol. Sometimes referred to as Threepio, he was fluent in over six million forms of communication, and developed a fussy and worry-prone personality throughout his many decades of operation. Along with his counterpart, the astromech droid R2-D2, C-3PO constantly found himself directly involved in pivotal moments of galactic history, and aided in saving the galaxy on many occasions.',
                                'url' => 'https://starwars.fandom.com/wiki/C-3PO',
                                'relatedUrl' => 'https://starwars.fandom.com/wiki/C-3PO/Legends',
                                'isCanon' => true,
                            ],
                            'model' => '3PO-series protocol droid',
                            'class' => 'Protocol droid',
                            'manufacturers' => [
                                [
                                    'name' => 'Cybot Galactica',
                                    'note' => null,
                                    'children' => [],
                                ],
                            ],
                            'factions' => [
                                [
                                    'name' => 'Galactic Republic',
                                    'note' => null,
                                    'children' => [
                                        // should not be imported
                                        [
                                            'name' => 'Galactic Senate',
                                            'note' => null,
                                        ],
                                    ],
                                ],
                                [
                                    'name' => 'Galactic Empire',
                                    'note' => null,
                                    'children' => [],
                                ],
                                [
                                    'name' => 'Alliance to Restore the Republic',
                                    'note' => null,
                                    'children' => [
                                        // should not be imported
                                        [
                                            'name' => 'Heroes of Yavin',
                                            'note' => null,
                                        ],
                                        // should not be imported
                                        [
                                            'name' => 'Endor strike team',
                                            'note' => null,
                                        ],
                                    ],
                                ],
                                // should not be imported
                                [
                                    'name' => 'Hutt Clan',
                                    'note' => null,
                                    'children' => [],
                                ],
                                // should not be imported
                                [
                                    'name' => 'Bright Tree tribe',
                                    'note' => null,
                                    'children' => [],
                                ],
                            ],
                            'technicalSpecifications' => [
                                [
                                    'name' => 'Height',
                                    'value' => '1.77 meters',
                                ],
                                [
                                    'name' => 'Mass',
                                    'value' => '75 kilograms',
                                ],
                                [
                                    'name' => 'Gender',
                                    'value' => 'Masculine programming',
                                ],
                            ],
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
                                    'name' => 'Dark Droids 1',
                                    'releaseDate' => 'August 2, 2023',
                                    'imageUrl' => 'https://images.placeholders.dev/?width=20&height=200',
                                    'type' => 'ComicBook',
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

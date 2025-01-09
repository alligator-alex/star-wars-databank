<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Providers;

use App\Modules\Databank\Common\Repositories\FactionRepository;
use App\Modules\Databank\Common\Repositories\LineRepository;
use App\Modules\Databank\Common\Repositories\ManufacturerRepository;
use App\Modules\Databank\Common\Repositories\VehicleRepository;
use App\Modules\Databank\Common\Repositories\MediaRepository;
use App\Modules\Databank\Import\Contracts\Importer;
use App\Modules\Databank\Import\Contracts\Parser;
use App\Modules\Databank\Import\Importer\WookieepediaImporter;
use App\Modules\Databank\Import\ImportLogger;
use App\Modules\Databank\Import\Parser\WookiepediaParser;
use Illuminate\Support\ServiceProvider;

class DatabankProvider extends ServiceProvider
{
    /**
     * @return class-string[]
     */
    public function provides(): array
    {
        return [
            Parser::class,
            Importer::class,
        ];
    }

    public function register(): void
    {
        $this->app->bind(Parser::class, static fn (): Parser => new WookiepediaParser(new ImportLogger()));

        $this->app->bind(Importer::class, static fn (): Importer => new WookieepediaImporter(
            new ImportLogger(),
            new VehicleRepository(),
            new LineRepository(),
            new ManufacturerRepository(),
            new FactionRepository(),
            new MediaRepository(),
        ));
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Sitemap\Console\Commands;

use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Sitemap\Common\Providers\DroidProvider;
use App\Modules\Sitemap\Common\Providers\StaticProvider;
use App\Modules\Sitemap\Common\Providers\VehicleProvider;
use App\Modules\Sitemap\Common\SitemapBuilder;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;
use Illuminate\Console\Command;

class BuildSitemap extends Command
{
    public const string DESCRIPTION = 'sitemap.xml generation';

    protected $signature = 'sitemap:build';

    public function __construct()
    {
        $this->description = self::DESCRIPTION;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        $this->output->writeln('sitemap.xml generation started');

        $builder = new SitemapBuilder(
            new StaticProvider(new FactionRepository(), new MediaRepository()),
            new VehicleProvider(new VehicleRepository()),
            new DroidProvider(new DroidRepository())
        );

        $builder->generate();

        $this->output->writeln('sitemap.xml successfully generated');
    }
}

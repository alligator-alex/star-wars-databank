<?php

declare(strict_types=1);

namespace App\Modules\Databank\Console\Commands;

use App\Modules\Databank\Import\Contracts\Importer;
use App\Modules\Databank\Import\Contracts\Parser;
use App\Modules\Databank\Import\Enums\EntityType;
use App\Modules\MessageBroker\Common\Contracts\Consumer;
use Illuminate\Console\Command;
use Throwable;

class Import extends Command
{
    protected $signature = 'databank:import {--skip-existing} {--type=}';

    public function __construct(
        private readonly Consumer $consumer,
        private readonly Parser $parser,
        private readonly Importer $importer
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $skipExisting = (bool) $this->option('skip-existing');
        $type = EntityType::tryFrom((string) $this->option('type'));

        $this->alert('Full import may take up to 3 hours to process over 197 000 pages!');

        if (!$skipExisting) {
            $this->warn('Also, new data will overwrite all your local changes.');
            $this->warn('To import new data only please use `--skip-existing` option.');
        }

        if (!$this->confirm('Are you sure you want to continue?')) {
            $this->error('Abort');
            return;
        }

        try {
            $this->importer->import(
                $this->parser->parse($this->consumer->getMessages(), $type),
                $skipExisting
            );

            $this->call('databank:disable-unused-relations');
        } catch (Throwable $e) {
            $this->error('Error: ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')');
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use SplFileObject;

class ExecuteOrder66 extends Command
{
    protected $signature = 'execute:order66';

    public function handle(): void
    {
        $this->output->newLine(2);

        $file = new SplFileObject(resource_path('misc/empire-logo.ascii'));
        while (!$file->eof()) {
            $this->output->write($file->fgets());
            usleep(30000);
        }
        $file = null;

        $this->output->newLine(2);
        $this->output->block(
            messages: 'The remaining Jedi will be hunted down and defeated!',
            style: 'fg=white;bg=red',
            padding: true
        );
    }
}

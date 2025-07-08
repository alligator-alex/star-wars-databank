<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import;

use App\Modules\Core\Common\Components\FileLogger;

class ImportLogger extends FileLogger
{
    public function getFileNameWithoutExtension(): string
    {
        return 'databank-import';
    }
}

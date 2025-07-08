<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Providers;

use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Relation::morphMap([
            1 => Vehicle::class,
            2 => Droid::class,
        ]);
    }
}

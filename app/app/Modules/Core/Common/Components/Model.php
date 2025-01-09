<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Components;

use App\Modules\Core\Common\Traits\GetTableName;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Orchid\Screen\AsSource;

/**
 * @template TFactory of Factory
 */
abstract class Model extends EloquentModel
{
    use GetTableName;
    use HasFactory;
    use AsSource;
}

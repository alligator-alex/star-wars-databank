<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Models;

use App\Modules\Core\Common\Components\Model;
use App\Modules\Core\Common\Traits\Publishable;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Factories\ManufacturerFactory;
use App\Modules\Databank\Common\Traits\HasDropdownList;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Orchid\Filters\Filterable;
use Orchid\Filters\HttpFilter;
use Orchid\Screen\AsSource;

/**
 * Manufacturer model.
 *
 * @extends Model<ManufacturerFactory>
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $slug
 * @property Status $status
 * @property int $sort
 * @method static Builder<static>|Manufacturer defaultSort(string $column, string $direction = 'asc')
 * @method static ManufacturerFactory factory($count = null, $state = [])
 * @method static Builder<static>|Manufacturer filters(?mixed $kit = null, ?HttpFilter $httpFilter = null)
 * @method static Builder<static>|Manufacturer filtersApply(iterable $filters = [])
 * @method static Builder<static>|Manufacturer filtersApplySelection($class)
 * @method static Builder<static>|Manufacturer findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder<static>|Manufacturer newModelQuery()
 * @method static Builder<static>|Manufacturer newQuery()
 * @method static Builder<static>|Manufacturer query()
 * @method static Builder<static>|Manufacturer whereCreatedAt($value)
 * @method static Builder<static>|Manufacturer whereId($value)
 * @method static Builder<static>|Manufacturer whereName($value)
 * @method static Builder<static>|Manufacturer whereSlug($value)
 * @method static Builder<static>|Manufacturer whereSort($value)
 * @method static Builder<static>|Manufacturer whereStatus($value)
 * @method static Builder<static>|Manufacturer whereUpdatedAt($value)
 * @method static Builder<static>|Manufacturer withUniqueSlugConstraints(Model $model, string $attribute, array $config, string $slug)
 */
class Manufacturer extends Model
{
    use AsSource;
    use Filterable;
    use HasDropdownList;
    use Publishable;
    use Sluggable;

    protected static string $factory = ManufacturerFactory::class;

    /** @var array<int, string> */
    protected array $allowedSorts = [
        'created_at',
        'updated_at',
        'id',
        'name',
        'sort',
    ];

    /**
     * @return array<string, array<string, string>>
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ]
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
        ];
    }
}

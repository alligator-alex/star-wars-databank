<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Models;

use App\Modules\Core\Common\Components\Model;
use App\Modules\Core\Common\Traits\Publishable;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Factories\LineFactory;
use App\Modules\Databank\Common\Traits\HasDropdownList;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Orchid\Filters\Filterable;
use Orchid\Filters\HttpFilter;
use Orchid\Screen\AsSource;

/**
 * Line model.
 *
 * @extends Model<LineFactory>
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $slug
 * @property Status $status
 * @property int $sort
 * @method static Builder<static>|Line defaultSort(string $column, string $direction = 'asc')
 * @method static LineFactory factory($count = null, $state = [])
 * @method static Builder<static>|Line filters(?mixed $kit = null, ?HttpFilter $httpFilter = null)
 * @method static Builder<static>|Line filtersApply(iterable $filters = [])
 * @method static Builder<static>|Line filtersApplySelection($class)
 * @method static Builder<static>|Line findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder<static>|Line newModelQuery()
 * @method static Builder<static>|Line newQuery()
 * @method static Builder<static>|Line query()
 * @method static Builder<static>|Line whereCreatedAt($value)
 * @method static Builder<static>|Line whereId($value)
 * @method static Builder<static>|Line whereName($value)
 * @method static Builder<static>|Line whereSlug($value)
 * @method static Builder<static>|Line whereSort($value)
 * @method static Builder<static>|Line whereStatus($value)
 * @method static Builder<static>|Line whereUpdatedAt($value)
 * @method static Builder<static>|Line withUniqueSlugConstraints(Model $model, string $attribute, array $config, string $slug)
 */
class Line extends Model
{
    use AsSource;
    use Filterable;
    use HasDropdownList;
    use Publishable;
    use Sluggable;

    protected static string $factory = LineFactory::class;

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
            ],
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

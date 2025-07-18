<?php

declare(strict_types=1);

namespace App\Modules\Faction\Common\Models;

use App\Modules\Core\Common\Components\Model;
use App\Modules\Core\Common\Traits\Publishable;
use App\Modules\Databank\Common\Contracts\Explorable;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Public\Enums\ExploreRootType;
use App\Modules\Faction\Common\Factories\FactionFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Orchid\Filters\Filterable;
use Orchid\Filters\HttpFilter;
use Orchid\Screen\AsSource;

/**
 * @extends Model<FactionFactory>
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $slug
 * @property Status $status
 * @property int $sort
 *
 * @method static Builder<static>|Faction defaultSort(string $column, string $direction = 'asc')
 * @method static FactionFactory factory($count = null, $state = [])
 * @method static Builder<static>|Faction filters(?mixed $kit = null, ?HttpFilter $httpFilter = null)
 * @method static Builder<static>|Faction filtersApply(iterable $filters = [])
 * @method static Builder<static>|Faction filtersApplySelection($class)
 * @method static Builder<static>|Faction findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder<static>|Faction newModelQuery()
 * @method static Builder<static>|Faction newQuery()
 * @method static Builder<static>|Faction query()
 * @method static Builder<static>|Faction whereCreatedAt($value)
 * @method static Builder<static>|Faction whereId($value)
 * @method static Builder<static>|Faction whereName($value)
 * @method static Builder<static>|Faction whereSlug($value)
 * @method static Builder<static>|Faction whereSort($value)
 * @method static Builder<static>|Faction whereStatus($value)
 * @method static Builder<static>|Faction whereUpdatedAt($value)
 * @method static Builder<static>|Faction withUniqueSlugConstraints(Model $model, string $attribute, array $config, string $slug)
 */
class Faction extends Model implements Explorable
{
    use AsSource;
    use Filterable;
    use Publishable;
    use Sluggable;

    protected static string $factory = FactionFactory::class;

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

    public function formattedName(): string
    {
        return Str::replace(
            ['to ', 'of ', 'the '],
            ['to&nbsp;', 'of&nbsp;', 'the&nbsp;'],
            $this->name,
            false
        );
    }

    public function explorableKey(): string
    {
        return $this->slug;
    }

    public function explorableType(): ExploreRootType
    {
        return ExploreRootType::FACTION;
    }
}

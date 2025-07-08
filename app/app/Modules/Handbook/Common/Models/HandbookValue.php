<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Models;

use App\Modules\Core\Common\Components\Model;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Factories\HandbookValueFactory;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Orchid\Filters\Filterable;
use Orchid\Filters\HttpFilter;

/**
 * Handbook value model.
 *
 * @extends Model<HandbookValueFactory>
 *
 * @property int $id
 * @property int $handbook_id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Handbook $handbook
 *
 * @method static Builder<static>|HandbookValue defaultSort(string $column, string $direction = 'asc')
 * @method static HandbookValueFactory factory($count = null, $state = [])
 * @method static Builder<static>|HandbookValue filters(?mixed $kit = null, ?HttpFilter $httpFilter = null)
 * @method static Builder<static>|HandbookValue filtersApply(iterable $filters = [])
 * @method static Builder<static>|HandbookValue filtersApplySelection($class)
 * @method static Builder<static>|HandbookValue findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder<static>|HandbookValue newModelQuery()
 * @method static Builder<static>|HandbookValue newQuery()
 * @method static Builder<static>|HandbookValue query()
 * @method static Builder<static>|HandbookValue whereCreatedAt($value)
 * @method static Builder<static>|HandbookValue whereHandbookId($value)
 * @method static Builder<static>|HandbookValue whereId($value)
 * @method static Builder<static>|HandbookValue whereName($value)
 * @method static Builder<static>|HandbookValue whereSlug($value)
 * @method static Builder<static>|HandbookValue whereUpdatedAt($value)
 * @method static Builder<static>|HandbookValue withUniqueSlugConstraints(Model $model, string $attribute, array $config, string $slug)
 */
class HandbookValue extends Model
{
    use Filterable;
    use Sluggable;

    protected static string $factory = HandbookValueFactory::class;

    /** @var array<int, string> */
    protected array $allowedSorts = [
        'created_at',
        'updated_at',
        'id',
        'name',
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

    public function handbook(): BelongsTo
    {
        return $this->belongsTo(Handbook::class, 'handbook_id', 'id');
    }

    /**
     * @param HandbookType $type
     * @param string $columnAsKey
     *
     * @return array<string, string>
     */
    public static function dropdownList(HandbookType $type, string $columnAsKey = 'id'): array
    {
        static $listCache = [];

        if (!isset($listCache[$type->value])) {
            $listCache[$type->value] = [];

            /** @var HandbookValueRepository $repository */
            $repository = app()->make(HandbookValueRepository::class);

            $query = $repository->queryBuilder()
                ->whereHas('handbook', static fn (Builder $subQuery) => $subQuery->where('type', '=', $type->value))
                ->orderBy('name')
                ->orderByDesc('id');

            /** @var static $item */
            foreach ($query->get() as $item) {
                $listCache[$type->value][$item->{$columnAsKey}] = $item->name;
            }

            // move "Other" to the end
            if ($type === HandbookType::VEHICLE_TYPE) {
                $otherKey = array_search('Other', $listCache[$type->value], true);

                if ($otherKey) {
                    $otherItem = $listCache[$type->value][$otherKey];
                    unset($listCache[$type->value][$otherKey]);
                    $listCache[$type->value][$otherKey] = $otherItem;
                }
            }
        }

        return $listCache[$type->value];
    }
}

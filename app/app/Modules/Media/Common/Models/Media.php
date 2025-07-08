<?php

declare(strict_types=1);

namespace App\Modules\Media\Common\Models;

use App\Modules\Core\Common\Components\Model;
use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Core\Common\Traits\Publishable;
use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Media\Common\Enums\MediaType;
use App\Modules\Media\Common\Factories\MediaFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Filters\HttpFilter;
use Orchid\Screen\AsSource;

/**
 * @extends Model<MediaFactory>
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $slug
 * @property Status $status
 * @property int $sort
 * @property MediaType|null $type
 * @property Carbon|null $release_date
 * @property int|null $poster_id
 *
 * @property-read Attachment|null $poster
 *
 * @method static Builder<static>|Media defaultSort(string $column, string $direction = 'asc')
 * @method static MediaFactory factory($count = null, $state = [])
 * @method static Builder<static>|Media filters(?mixed $kit = null, ?HttpFilter $httpFilter = null)
 * @method static Builder<static>|Media filtersApply(iterable $filters = [])
 * @method static Builder<static>|Media filtersApplySelection($class)
 * @method static Builder<static>|Media findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder<static>|Media newModelQuery()
 * @method static Builder<static>|Media newQuery()
 * @method static Builder<static>|Media query()
 * @method static Builder<static>|Media whereCreatedAt($value)
 * @method static Builder<static>|Media whereId($value)
 * @method static Builder<static>|Media whereName($value)
 * @method static Builder<static>|Media wherePosterId($value)
 * @method static Builder<static>|Media whereReleaseDate($value)
 * @method static Builder<static>|Media whereSlug($value)
 * @method static Builder<static>|Media whereSort($value)
 * @method static Builder<static>|Media whereStatus($value)
 * @method static Builder<static>|Media whereType($value)
 * @method static Builder<static>|Media whereUpdatedAt($value)
 * @method static Builder<static>|Media withUniqueSlugConstraints(Model $model, string $attribute, array $config, string $slug)
 */
class Media extends Model
{
    use AsSource;
    use Attachable;
    use Filterable;
    use Publishable;
    use Sluggable;

    public $table = 'media';

    protected static string $factory = MediaFactory::class;

    /** @var array<int, string> */
    protected array $allowedSorts = [
        'created_at',
        'updated_at',
        'id',
        'name',
        'sort',
        'type',
        'release_date',
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
            'type' => MediaType::class,
            'release_date' => 'datetime:Y-m-d',
        ];
    }

    public function poster(): HasOne
    {
        return $this->hasOne(Attachment::class, 'id', 'poster_id')
            ->where('group', '=', AttachmentGroup::MEDIA_POSTER->value);
    }

    public function releaseYear(): ?string
    {
        return $this->release_date?->format('Y');
    }

    public function nameWithReleaseYear(): string
    {
        if (!$this->release_date) {
            return $this->name;
        }

        return $this->name . ' (' . $this->releaseYear() . ')';
    }

    /**
     * @param bool $withDrafts
     * @param string $columnAsKey
     *
     * @return array<string, array<int, string>>
     */
    public static function dropdownList(bool $withDrafts = false, string $columnAsKey = 'id'): array
    {
        static $list = null;

        if ($list === null) {
            $otherKey = __('Other');

            $list = [
                MediaType::MOVIE->nameForHumans() => [],
                MediaType::SERIES->nameForHumans() => [],
                MediaType::GAME->nameForHumans() => [],
                $otherKey => [],
            ];

            /** @var Builder|static $query */
            $query = static::query();

            if ($withDrafts) {
                $query->withDrafts();
            }

            $query->orderBy('sort')->orderBy('release_date')->orderBy('name')->orderByDesc('id');

            /** @var static $item */
            foreach ($query->get() as $item) {
                $value = $item->name;

                if ($item->release_date) {
                    $value .= ' (' . $item->release_date->format('Y') . ')';
                }

                if (!$item->type) {
                    $list[$otherKey][$item->{$columnAsKey}] = $value;
                    continue;
                }

                $list[$item->type->nameForHumans()][$item->{$columnAsKey}] = $value;
            }

            foreach ($list as $type => $names) {
                if (empty($names)) {
                    unset($list[$type]);
                }
            }
        }

        return (array) $list;
    }
}

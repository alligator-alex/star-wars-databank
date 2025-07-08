<?php

declare(strict_types=1);

namespace App\Modules\Droid\Common\Models;

use App\Modules\Core\Common\Components\Model;
use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Core\Common\Traits\Publishable;
use App\Modules\Databank\Common\DTOs\PageSettings;
use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Droid\Common\DTOs\TechSpecs;
use App\Modules\Droid\Common\Factories\DroidFactory;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Faction\Common\Traits\HasFactions;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Manufacturer\Common\Traits\HasManufacturers;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Common\Traits\HasAppearances;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Filters\HttpFilter;
use Orchid\Screen\AsSource;

/**
 * Droid model.
 *
 * @extends Model<DroidFactory>
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $slug
 * @property Status $status
 * @property int $sort
 * @property string|null $external_url
 * @property bool $canon
 * @property int|null $line_id
 * @property int|null $model_id
 * @property int|null $class_id
 * @property int|null $image_id
 * @property string|null $description
 * @property array<array-key, mixed>|null $technical_specifications
 * @property array<array-key, mixed>|null $page_settings
 *
 * @property-read Collection<int, Media> $appearances
 * @property-read int|null $appearances_count
 * @property-read HandbookValue|null $class
 * @property-read Collection<int, Faction> $factions
 * @property-read int|null $factions_count
 * @property-read Attachment|null $image
 * @property-read HandbookValue|null $line
 * @property-read Faction|null $mainFaction
 * @property-read Collection<int, Manufacturer> $manufacturers
 * @property-read int|null $manufacturers_count
 * @property-read HandbookValue|null $model
 * @property-read Collection<int, Faction> $otherFactions
 * @property-read int|null $other_factions_count
 *
 * @method static Builder<static>|Droid defaultSort(string $column, string $direction = 'asc')
 * @method static DroidFactory factory($count = null, $state = [])
 * @method static Builder<static>|Droid filters(?mixed $kit = null, ?HttpFilter $httpFilter = null)
 * @method static Builder<static>|Droid filtersApply(iterable $filters = [])
 * @method static Builder<static>|Droid filtersApplySelection($class)
 * @method static Builder<static>|Droid findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder<static>|Droid newModelQuery()
 * @method static Builder<static>|Droid newQuery()
 * @method static Builder<static>|Droid query()
 * @method static Builder<static>|Droid whereCanon($value)
 * @method static Builder<static>|Droid whereClassId($value)
 * @method static Builder<static>|Droid whereCreatedAt($value)
 * @method static Builder<static>|Droid whereDescription($value)
 * @method static Builder<static>|Droid whereExternalUrl($value)
 * @method static Builder<static>|Droid whereId($value)
 * @method static Builder<static>|Droid whereImageId($value)
 * @method static Builder<static>|Droid whereLineId($value)
 * @method static Builder<static>|Droid whereModelId($value)
 * @method static Builder<static>|Droid whereName($value)
 * @method static Builder<static>|Droid wherePageSettings($value)
 * @method static Builder<static>|Droid whereSlug($value)
 * @method static Builder<static>|Droid whereSort($value)
 * @method static Builder<static>|Droid whereStatus($value)
 * @method static Builder<static>|Droid whereTechnicalSpecifications($value)
 * @method static Builder<static>|Droid whereUpdatedAt($value)
 * @method static Builder<static>|Droid withUniqueSlugConstraints(Model $model, string $attribute, array $config, string $slug)
 */
class Droid extends Model
{
    use AsSource;
    use Attachable;
    use Filterable;
    use HasAppearances;
    use HasFactions;
    use HasManufacturers;
    use Publishable;
    use Sluggable;

    protected static string $factory = DroidFactory::class;

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
            'technical_specifications' => 'array',
            'page_settings' => 'array',
        ];
    }

    /**
     * @return HasOne<Attachment, covariant self>
     */
    public function image(): HasOne
    {
        return $this->hasOne(Attachment::class, 'id', 'image_id')
            ->where('group', '=', AttachmentGroup::DROID_IMAGE->value);
    }

    /**
     * Line.
     *
     * @return BelongsTo<HandbookValue, covariant self>
     */
    public function line(): BelongsTo
    {
        return $this->belongsTo(HandbookValue::class, 'line_id', 'id')
            ->with([
                'handbook' => static fn (Relation $subQuery): Relation => $subQuery->where(
                    Handbook::tableName() . '.type',
                    '=',
                    HandbookType::DROID_LINE->value
                )
            ]);
    }

    /**
     * Model.
     *
     * @return BelongsTo<HandbookValue, covariant self>
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(HandbookValue::class, 'model_id', 'id')
            ->with([
                'handbook' => static fn (Relation $subQuery): Relation => $subQuery->where(
                    Handbook::tableName() . '.type',
                    '=',
                    HandbookType::DROID_MODEL->value
                )
            ]);
    }

    /**
     * Class.
     *
     * @return BelongsTo<HandbookValue, covariant self>
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(HandbookValue::class, 'class_id', 'id')
            ->with([
                'handbook' => static fn (Relation $subQuery): Relation => $subQuery->where(
                    Handbook::tableName() . '.type',
                    '=',
                    HandbookType::DROID_CLASS->value
                )
            ]);
    }

    /**
     * Technical specifications depending on category.
     *
     * @return TechSpecs|null
     */
    public function getTechnicalSpecifications(): ?TechSpecs
    {
        if (empty((array) $this->technical_specifications)) {
            return null;
        }

        return TechSpecs::hydrate((array) $this->technical_specifications);
    }

    public function getPageSettings(): PageSettings
    {
        return PageSettings::hydrate((array) $this->page_settings);
    }
}

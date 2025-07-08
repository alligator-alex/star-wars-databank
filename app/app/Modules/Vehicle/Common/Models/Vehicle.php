<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Common\Models;

use App\Modules\Core\Common\Components\Model;
use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Core\Common\Traits\Publishable;
use App\Modules\Databank\Common\DTOs\PageSettings;
use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Faction\Common\Traits\HasFactions;
use App\Modules\Handbook\Common\Enums\HandbookType;
use App\Modules\Handbook\Common\Models\Handbook;
use App\Modules\Handbook\Common\Models\HandbookValue;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Manufacturer\Common\Traits\HasManufacturers;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Common\Traits\HasAppearances;
use App\Modules\Sitemap\Common\Contracts\Sitemappable;
use App\Modules\Vehicle\Common\DTOs\TechSpecs\CategorySpecificTechSpecs;
use App\Modules\Vehicle\Common\Factories\VehicleFactory;
use App\Modules\Vehicle\Common\Helpers\VehicleHelper;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;
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
 * Vehicle model.
 *
 * @extends Model<VehicleFactory>
 *
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
 * @property int|null $image_id
 * @property string|null $description
 * @property array<array-key, mixed>|null $technical_specifications
 * @property array<array-key, mixed>|null $page_settings
 * @property int|null $category_id
 * @property int|null $type_id
 * @property HandbookValue|null $category
 * @property HandbookValue|null $type
 *
 * @property-read Collection<int, Media> $appearances
 * @property-read int|null $appearances_count
 * @property-read Collection<int, Faction> $factions
 * @property-read int|null $factions_count
 * @property-read Attachment|null $image
 * @property-read HandbookValue|null $line
 * @property-read Faction|null $mainFaction
 * @property-read Collection<int, Manufacturer> $manufacturers
 * @property-read int|null $manufacturers_count
 * @property-read Collection<int, Faction> $otherFactions
 * @property-read int|null $other_factions_count
 *
 * @method static Builder<static>|Vehicle defaultSort(string $column, string $direction = 'asc')
 * @method static VehicleFactory factory($count = null, $state = [])
 * @method static Builder<static>|Vehicle filters(?mixed $kit = null, ?HttpFilter $httpFilter = null)
 * @method static Builder<static>|Vehicle filtersApply(iterable $filters = [])
 * @method static Builder<static>|Vehicle filtersApplySelection($class)
 * @method static Builder<static>|Vehicle findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder<static>|Vehicle newModelQuery()
 * @method static Builder<static>|Vehicle newQuery()
 * @method static Builder<static>|Vehicle query()
 * @method static Builder<static>|Vehicle whereCanon($value)
 * @method static Builder<static>|Vehicle whereCategoryId($value)
 * @method static Builder<static>|Vehicle whereCreatedAt($value)
 * @method static Builder<static>|Vehicle whereDescription($value)
 * @method static Builder<static>|Vehicle whereExternalUrl($value)
 * @method static Builder<static>|Vehicle whereId($value)
 * @method static Builder<static>|Vehicle whereImageId($value)
 * @method static Builder<static>|Vehicle whereLineId($value)
 * @method static Builder<static>|Vehicle whereName($value)
 * @method static Builder<static>|Vehicle wherePageSettings($value)
 * @method static Builder<static>|Vehicle whereSlug($value)
 * @method static Builder<static>|Vehicle whereSort($value)
 * @method static Builder<static>|Vehicle whereStatus($value)
 * @method static Builder<static>|Vehicle whereTechnicalSpecifications($value)
 * @method static Builder<static>|Vehicle whereTypeId($value)
 * @method static Builder<static>|Vehicle whereUpdatedAt($value)
 * @method static Builder<static>|Vehicle withUniqueSlugConstraints(Model $model, string $attribute, array $config, string $slug)
 */
class Vehicle extends Model implements Sitemappable
{
    use AsSource;
    use Attachable;
    use Filterable;
    use HasAppearances;
    use HasFactions;
    use HasManufacturers;
    use Publishable;
    use Sluggable;

    protected static string $factory = VehicleFactory::class;

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
            ->where('group', '=', AttachmentGroup::VEHICLE_IMAGE->value);
    }

    /**
     * Category.
     *
     * @return BelongsTo<HandbookValue, covariant self>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(HandbookValue::class, 'category_id', 'id')
            ->with([
                'handbook' => static fn (Relation $subQuery): Relation => $subQuery->where(
                    Handbook::tableName() . '.type',
                    '=',
                    HandbookType::VEHICLE_CATEGORY->value
                )
            ]);
    }

    /**
     * Type.
     *
     * @return BelongsTo<HandbookValue, covariant self>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(HandbookValue::class, 'type_id', 'id')
            ->with([
                'handbook' => static fn (Relation $subQuery): Relation => $subQuery->where(
                    Handbook::tableName() . '.type',
                    '=',
                    HandbookType::VEHICLE_TYPE->value
                )
            ]);
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
                    HandbookType::VEHICLE_LINE->value
                )
            ]);
    }

    /**
     * Technical specifications depending on category.
     *
     * @return CategorySpecificTechSpecs|null
     */
    public function getTechnicalSpecifications(): ?CategorySpecificTechSpecs
    {
        if (!$this->category) {
            return null;
        }

        return VehicleHelper::hydrateTechSpecs($this->category, (array) $this->technical_specifications);
    }

    public function getPageSettings(): PageSettings
    {
        return PageSettings::hydrate((array) $this->page_settings);
    }

    public function getSitemapUrl(): ?string
    {
        return route(VehicleRouteName::DETAIL, ['slug' => $this->slug]);
    }

    public function getSitemapModificationDate(): ?Carbon
    {
        return $this->updated_at;
    }
}

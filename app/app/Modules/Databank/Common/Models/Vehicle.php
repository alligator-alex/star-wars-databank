<?php

declare(strict_types=1);

namespace App\Modules\Databank\Common\Models;

use App\Modules\Core\Common\Components\Model;
use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Core\Common\Traits\Publishable;
use App\Modules\Databank\Common\DTOs\CategorySpecificTechSpecs;
use App\Modules\Databank\Common\DTOs\VehiclePageSettings;
use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Common\Enums\VehicleType;
use App\Modules\Databank\Common\Factories\VehicleFactory;
use App\Modules\Databank\Common\Helpers\VehicleHelper;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Filters\HttpFilter;
use Orchid\Screen\AsSource;

/**
 * Vehicle model.
 *
 * @extends Model<VehicleFactory>
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $slug
 * @property Status $status
 * @property int $sort
 * @property string|null $external_url
 * @property bool $canon
 * @property VehicleCategory|null $category
 * @property VehicleType|null $type
 * @property int|null $line_id
 * @property int|null $image_id
 * @property string|null $description
 * @property array|null $technical_specifications
 * @property array|null $page_settings
 * @property-read Collection<int, Media> $appearances
 * @property-read int|null $appearances_count
 * @property-read Collection<int, Faction> $factions
 * @property-read int|null $factions_count
 * @property-read Attachment|null $image
 * @property-read Line|null $line
 * @property-read Faction|null $mainFaction
 * @property-read Collection<int, Manufacturer> $manufacturers
 * @property-read int|null $manufacturers_count
 * @property-read Collection<int, Faction> $otherFactions
 * @property-read int|null $other_factions_count
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
 * @method static Builder<static>|Vehicle whereCategory($value)
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
 * @method static Builder<static>|Vehicle whereType($value)
 * @method static Builder<static>|Vehicle whereUpdatedAt($value)
 * @method static Builder<static>|Vehicle withUniqueSlugConstraints(Model $model, string $attribute, array $config, string $slug)
 */
class Vehicle extends Model
{
    use AsSource;
    use Attachable;
    use Filterable;
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
            'category' => VehicleCategory::class,
            'type' => VehicleType::class,
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
     * Factions.
     *
     * @return BelongsToMany<Faction, covariant self>
     */
    public function factions(): BelongsToMany
    {
        $table = Faction::tableName();

        return $this->belongsToMany(Faction::class, VehicleFaction::tableName())
            ->orderByPivot(VehicleFaction::tableName() . '.main', 'desc')
            ->orderBy($table . '.sort')
            ->orderByDesc($table . '.id');
    }

    /**
     * Main Faction.
     *
     * @return HasOneThrough<Faction, VehicleFaction, covariant self>
     */
    public function mainFaction(): HasOneThrough
    {
        return $this->hasOneThrough(
            Faction::class,
            VehicleFaction::class,
            'vehicle_id',
            'id',
            'id',
            'faction_id'
        )->where(VehicleFaction::tableName() . '.main', '=', true);
    }

    /**
     * Other factions (except main one).
     *
     * @return BelongsToMany<Faction, covariant self>
     */
    public function otherFactions(): BelongsToMany
    {
        return $this->factions()->whereNot(VehicleFaction::tableName() . '.main', '=', true);
    }

    /**
     * Manufacturers.
     *
     * @return BelongsToMany<Manufacturer, covariant self>
     */
    public function manufacturers(): BelongsToMany
    {
        $table = Manufacturer::tableName();

        return $this->belongsToMany(Manufacturer::class, VehicleManufacturer::tableName())
            ->orderBy($table . '.name')
            ->orderBy($table . '.sort')
            ->orderByDesc($table . '.id');
    }

    /**
     * Line.
     *
     * @return BelongsTo<Line, covariant self>
     */
    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }

    /**
     * Appearances.
     *
     * @return BelongsToMany<Media, covariant self>
     */
    public function appearances(): BelongsToMany
    {
        $table = Media::tableName();

        return $this->belongsToMany(Media::class, VehicleAppearance::tableName())
            ->orderBy($table . '.sort')
            ->orderBy($table . '.release_date')
            ->orderBy($table . '.name')
            ->orderByDesc($table . '.id');
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

    public function getPageSettings(): VehiclePageSettings
    {
        return VehiclePageSettings::hydrate((array) $this->page_settings);
    }
}

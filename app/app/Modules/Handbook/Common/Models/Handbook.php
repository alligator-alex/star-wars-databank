<?php

declare(strict_types=1);

namespace App\Modules\Handbook\Common\Models;

use App\Modules\Core\Common\Components\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Handbook model.
 *
 * @property int $id
 * @property int $type
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, HandbookValue> $values
 * @property-read int|null $values_count
 *
 * @method static Builder<static>|Handbook newModelQuery()
 * @method static Builder<static>|Handbook newQuery()
 * @method static Builder<static>|Handbook query()
 * @method static Builder<static>|Handbook whereCreatedAt($value)
 * @method static Builder<static>|Handbook whereId($value)
 * @method static Builder<static>|Handbook whereName($value)
 * @method static Builder<static>|Handbook whereType($value)
 * @method static Builder<static>|Handbook whereUpdatedAt($value)
 */
class Handbook extends Model
{
    public function values(): HasMany
    {
        return $this->hasMany(HandbookValue::class, 'handbook_id', 'id');
    }
}

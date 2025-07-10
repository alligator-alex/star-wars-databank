<?php

declare(strict_types=1);

namespace App\Modules\Faction\Common\Traits;

use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Faction\Common\Models\Pivots\Factionable;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasFactions
{
    /**
     * Factions.
     *
     * @return MorphToMany<Faction, covariant self>
     */
    public function factions(): MorphToMany
    {
        $table = Faction::tableName();

        return $this->morphToMany(Faction::class, Factionable::RELATION)
            ->orderByPivot(Factionable::tableName() . '.main', 'desc')
            ->orderBy($table . '.sort')
            ->orderByDesc($table . '.id');
    }

    /**
     * Main Faction.
     *
     * @return HasOneThrough<Faction, Factionable, covariant self>
     */
    public function mainFaction(): HasOneThrough
    {
        return $this->hasOneThrough(
            Faction::class,
            Factionable::class,
            Factionable::RELATION . '_id',
            'id',
            'id',
            'faction_id'
        )->where(Factionable::RELATION . '_type', '=', $this->getMorphClass())
            ->where(Factionable::tableName() . '.main', '=', true);
    }

    /**
     * Other factions (except main one).
     *
     * @return MorphToMany<Faction, covariant self, Factionable>
     */
    public function otherFactions(): MorphToMany
    {
        /** @var MorphToMany<Faction, covariant self, Factionable> $query */
        $query = $this->factions()->whereNot(Factionable::tableName() . '.main', '=', true);

        return $query;
    }
}

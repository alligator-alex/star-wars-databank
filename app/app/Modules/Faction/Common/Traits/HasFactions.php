<?php

declare(strict_types=1);

namespace App\Modules\Faction\Common\Traits;

use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Faction\Common\Models\Pivots\Factionable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
            'factionable_id',
            'id',
            'id',
            'faction_id'
        )->where(Factionable::tableName() . '.main', '=', true);
    }

    /**
     * Other factions (except main one).
     *
     * @return BelongsToMany<Faction, covariant self>
     */
    public function otherFactions(): BelongsToMany
    {
        return $this->factions()
            ->whereNot(Factionable::tableName() . '.main', '=', true);
    }
}

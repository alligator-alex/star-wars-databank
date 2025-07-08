<?php

declare(strict_types=1);

namespace App\Modules\Media\Common\Traits;

use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Common\Models\Pivots\Mediable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasAppearances
{
    /**
     * Appearances.
     *
     * @return MorphToMany<Media, covariant self>
     */
    public function appearances(): MorphToMany
    {
        $table = Media::tableName();

        return $this->morphToMany(Media::class, Mediable::RELATION)
            ->orderBy($table . '.sort')
            ->orderBy($table . '.release_date')
            ->orderBy($table . '.name')
            ->orderByDesc($table . '.id');
    }
}

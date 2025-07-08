<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Traits;

use App\Modules\Core\Common\Scopes\PublishableScope;
use App\Modules\Databank\Common\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Builder<static> withDrafts()
 * @method Builder<static> withoutDrafts()
 * @method Builder<static> onlyDrafts()
 *
 * @mixin Model
 */
trait Publishable
{
    public static function bootPublishable(): void
    {
        static::addGlobalScope(new PublishableScope());
    }

    public function isPublished(): bool
    {
        return ($this->status === Status::PUBLISHED);
    }
}

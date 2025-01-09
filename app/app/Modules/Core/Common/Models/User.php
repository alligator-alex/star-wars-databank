<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Models;

use App\Modules\Core\Admin\Presenters\UserPresenter;
use App\Modules\Core\Common\Factories\UserFactory;
use App\Modules\Core\Common\Traits\GetTableName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Carbon;
use Orchid\Filters\HttpFilter;
use Orchid\Platform\Models\Role;
use Orchid\Platform\Models\User as Authenticatable;

/**
 * User model.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array|null $permissions
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @method static Builder<static>|User averageByDays(string $value, $startDate = null, $stopDate = null, ?string $dateColumn = null)
 * @method static Builder<static>|User byAccess(string $permitWithoutWildcard)
 * @method static Builder<static>|User byAnyAccess($permitsWithoutWildcard)
 * @method static Builder<static>|User countByDays($startDate = null, $stopDate = null, ?string $dateColumn = null)
 * @method static Builder<static>|User countForGroup(string $groupColumn)
 * @method static Builder<static>|User defaultSort(string $column, string $direction = 'asc')
 * @method static UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User filters(?mixed $kit = null, ?HttpFilter $httpFilter = null)
 * @method static Builder<static>|User filtersApply(iterable $filters = [])
 * @method static Builder<static>|User filtersApplySelection($class)
 * @method static Builder<static>|User maxByDays(string $value, $startDate = null, $stopDate = null, ?string $dateColumn = null)
 * @method static Builder<static>|User minByDays(string $value, $startDate = null, $stopDate = null, ?string $dateColumn = null)
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User sumByDays(string $value, $startDate = null, $stopDate = null, ?string $dateColumn = null)
 * @method static Builder<static>|User valuesByDays(string $value, $startDate = null, $stopDate = null, string $dateColumn = 'created_at')
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User wherePermissions($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 */
class User extends Authenticatable
{
    use GetTableName;
    use HasFactory;

    protected static string $factory = UserFactory::class;

    public function presenter(): UserPresenter
    {
        return new UserPresenter($this);
    }
}

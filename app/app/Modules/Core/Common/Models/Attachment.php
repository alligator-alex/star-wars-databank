<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Models;

use App\Modules\Core\Common\Factories\AttachmentFactory;
use App\Modules\Core\Common\Traits\GetTableName;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\Models\Attachment as OrchidAttachment;
use Orchid\Attachment\Models\Attachmentable;
use Orchid\Filters\HttpFilter;

/**
 * Attachment model
 *
 * @property int $id
 * @property string $name
 * @property string $original_name
 * @property string $mime
 * @property string|null $extension
 * @property int $size
 * @property int $sort
 * @property string $path
 * @property string|null $description
 * @property string|null $alt
 * @property string|null $hash
 * @property string $disk
 * @property int|null $user_id
 * @property string|null $group
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $medium_url
 * @property-read string|null $relative_url
 * @property-read string|null $thumb_url
 * @property-read string|null $title
 * @property-read string|null $url
 * @property-read Collection<int, Attachmentable> $relationships
 * @property-read int|null $relationships_count
 * @property-read User|null $user
 * @method static Builder<static>|Attachment defaultSort(string $column, string $direction = 'asc')
 * @method static AttachmentFactory factory($count = null, $state = [])
 * @method static Builder<static>|Attachment filters(?mixed $kit = null, ?HttpFilter $httpFilter = null)
 * @method static Builder<static>|Attachment filtersApply(iterable $filters = [])
 * @method static Builder<static>|Attachment filtersApplySelection($class)
 * @method static Builder<static>|Attachment newModelQuery()
 * @method static Builder<static>|Attachment newQuery()
 * @method static Builder<static>|Attachment query()
 * @method static Builder<static>|Attachment sorted($direction = 'ASC')
 * @method static Builder<static>|Attachment whereAlt($value)
 * @method static Builder<static>|Attachment whereCreatedAt($value)
 * @method static Builder<static>|Attachment whereDescription($value)
 * @method static Builder<static>|Attachment whereDisk($value)
 * @method static Builder<static>|Attachment whereExtension($value)
 * @method static Builder<static>|Attachment whereGroup($value)
 * @method static Builder<static>|Attachment whereHash($value)
 * @method static Builder<static>|Attachment whereId($value)
 * @method static Builder<static>|Attachment whereMime($value)
 * @method static Builder<static>|Attachment whereName($value)
 * @method static Builder<static>|Attachment whereOriginalName($value)
 * @method static Builder<static>|Attachment wherePath($value)
 * @method static Builder<static>|Attachment whereSize($value)
 * @method static Builder<static>|Attachment whereSort($value)
 * @method static Builder<static>|Attachment whereUpdatedAt($value)
 * @method static Builder<static>|Attachment whereUserId($value)
 */
class Attachment extends OrchidAttachment
{
    use GetTableName;
    use HasFactory;

    protected static string $factory = AttachmentFactory::class;

    public function url(?string $default = null, ?string $preset = null): ?string
    {
        /** @var Filesystem|Cloud $disk */
        $disk = Storage::disk($this->getAttribute('disk'));
        $path = $this->physicalPath();

        if (is_null($path) || !$disk->exists($path)) {
            return $default;
        }

        if ($preset) {
            $path = $preset . DIRECTORY_SEPARATOR . $path;
        }

        return config('imgproxy.public_url') . $path;
    }

    public function getThumbUrlAttribute(): ?string
    {
        return $this->url(preset: 'thumb');
    }

    public function getMediumUrlAttribute(): ?string
    {
        return $this->url(preset: 'medium');
    }

    protected static function newFactory(): AttachmentFactory
    {
        return new AttachmentFactory();
    }
}

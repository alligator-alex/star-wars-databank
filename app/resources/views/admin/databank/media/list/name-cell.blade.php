@php
use App\Modules\Databank\Admin\Components\Media\Layouts\List\ListTable;
use App\Modules\Databank\Admin\Enums\MediaRouteName;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Models\Media;

/**
 * @var Media $model
 */
@endphp
<a href="{{ route(MediaRouteName::ONE, $model->id, false) }}"
   class="@if (!$model->isPublished()) text-muted @endif">
    <div class="form-group mb-1">
        <b>{{ Str::limit($model->name, ListTable::NAME_SYMBOL_LIMIT) }}</b>@if ($model->release_date)
            ({{ $model->releaseYear() }})
        @endif
    </div>
    <div class="form-group mb-0">
        <span
            class="badge rounded-pill {{ ($model->status === Status::PUBLISHED) ? 'bg-success' : 'bg-danger' }}">{{ $model->status->nameForHumans() }}</span>
        <span class="ms-1 text-muted">{{ $model->type?->nameForHumans() }}</span>
    </div>
</a>

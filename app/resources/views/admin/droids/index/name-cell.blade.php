@php
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Droid\Common\Models\Droid;

/**
 * @var Droid $model
 */
@endphp
<a href="{{ route(DroidRouteName::EDIT, $model->id, false) }}"
   class="@if (!$model->isPublished()) text-muted @endif">
    <div class="form-group mb-1">
        <b>{{ $model->name }}</b>
    </div>
    <div class="form-group mb-0">
        <span class="badge rounded-pill {{ ($model->status === Status::PUBLISHED) ? 'bg-success' : 'bg-danger' }}">{{ $model->status->nameForHumans() }}</span>
        <span class="ms-1 text-muted">{{ $model->line?->name ?? '-' }} / {{ $model->model?->name ?? '-' }}</span>
    </div>
</a>

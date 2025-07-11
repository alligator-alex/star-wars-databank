@php
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Common\Models\Vehicle;

/**
 * @var Vehicle $model
 */
@endphp
<a href="{{ route(VehicleRouteName::EDIT, $model->id, false) }}"
   class="@if (!$model->isPublished()) text-muted @endif">
    <div class="form-group mb-1">
        <b>{{ $model->name }}</b>
    </div>
    <div class="form-group mb-0">
        <span class="badge rounded-pill {{ ($model->status === Status::PUBLISHED) ? 'bg-success' : 'bg-danger' }}">{{ $model->status->nameForHumans() }}</span>
        <span class="ms-1 text-muted">{{ $model->category?->name ?? '-' }} / {{ $model->type?->name ?? '-' }}</span>
    </div>
</a>

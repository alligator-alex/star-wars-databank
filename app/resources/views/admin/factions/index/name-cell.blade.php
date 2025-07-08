@php
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Faction\Admin\Enums\FactionRouteName;
use App\Modules\Faction\Common\Models\Faction;

/**
 * @var Faction $model
 */
@endphp
<a href="{{ route(FactionRouteName::EDIT, $model->id, false) }}"
   class="@if (!$model->isPublished()) text-muted @endif">
    <div class="form-group mb-1">
        <b>{{ $model->name }}</b>
    </div>
    <div class="form-group mb-0">
        <span class="badge rounded-pill {{ ($model->status === Status::PUBLISHED) ? 'bg-success' : 'bg-danger' }}">{{ $model->status->nameForHumans() }}</span>
    </div>
</a>

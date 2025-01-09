@php
use App\Modules\Databank\Admin\Components\Vehicle\Layouts\List\ListTable;
use App\Modules\Databank\Admin\Enums\VehicleRouteName;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Common\Models\Vehicle;

/**
 * @var Vehicle $model
 */
@endphp
<a href="{{ route(VehicleRouteName::ONE, $model->id, false) }}"
   class="@if (!$model->isPublished()) text-muted @endif">
    <div class="form-group mb-1">
        <b>{{ Str::limit($model->name, ListTable::NAME_SYMBOL_LIMIT) }}</b>
    </div>
    <div class="form-group mb-0">
        <span class="badge rounded-pill {{ ($model->status === Status::PUBLISHED) ? 'bg-success' : 'bg-danger' }}">{{ $model->status->nameForHumans() }}</span>
        <span class="ms-1 text-muted">{{ $model->category?->nameForHumans() ?? '-' }} / {{ $model->type?->nameForHumans() ?? __('(None)') }}</span>
    </div>
</a>


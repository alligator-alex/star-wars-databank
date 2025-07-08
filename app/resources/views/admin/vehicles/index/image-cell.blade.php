@php
use App\Modules\Vehicle\Admin\Enums\VehicleRouteName;
use App\Modules\Vehicle\Common\Models\Vehicle;

/**
 * @var Vehicle $model
 */
@endphp
<a href="{{ route(VehicleRouteName::EDIT, $model->id, false) }}" class="text-muted">
    @if ($model->image)
        <div class="form-group mb-1">
            <picture class="index-image-preview">
                <img src="{{ $model->image->thumb_url }}" alt="{{ $model->name }}">
            </picture>
        </div>
    @endif
    <div class="form-group mb-0">
        <span># {{$model->id }}</span>
    </div>
</a>

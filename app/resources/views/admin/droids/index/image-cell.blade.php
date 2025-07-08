@php
use App\Modules\Droid\Admin\Enums\DroidRouteName;
use App\Modules\Droid\Common\Models\Droid;

/**
 * @var Droid $model
 */
@endphp
<a href="{{ route(DroidRouteName::EDIT, $model->id, false) }}" class="text-muted">
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

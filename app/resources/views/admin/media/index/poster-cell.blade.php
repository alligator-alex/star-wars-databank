@php
use App\Modules\Media\Admin\Enums\MediaRouteName;
use App\Modules\Media\Common\Models\Media;

/**
 * @var Media $model
 */
@endphp
<a href="{{ route(MediaRouteName::EDIT, $model->id, false) }}" class="text-muted">
    @if ($model->poster)
        <div class="form-group mb-1">
            <picture class="index-image-preview">
                <img src="{{ $model->poster->thumb_url }}" alt="{{ $model->name }}">
            </picture>
        </div>
    @endif
    <div class="form-group mb-0">
        <span># {{$model->id }}</span>
    </div>
</a>

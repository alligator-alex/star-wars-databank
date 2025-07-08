@php
use App\Modules\Handbook\Admin\Enums\HandbookValueRouteName;
use App\Modules\Handbook\Common\Models\HandbookValue;

/**
 * @var HandbookValue $model
 */
@endphp
<a href="{{ route(HandbookValueRouteName::EDIT->value, [
        'handbookId' => $model->handbook->id,
        'handbookValueId' => $model->id,
    ], false) }}">
    <div class="form-group mb-1">
        <b>{{ $model->name }}</b>
    </div>
</a>

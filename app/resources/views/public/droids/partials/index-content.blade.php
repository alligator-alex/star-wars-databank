@php
use App\Modules\Droid\Common\Models\Droid;
use Illuminate\Support\Collection;

/**
 * @var Collection<int, Droid> $droids
 */

$i = 0;
@endphp
@forelse($droids as $droid)
    @php
    $isPreviewTarget = ((int) request()->get('preview-target') === $droid->id);
    @endphp
    @include('public.droids.partials.item-content', [
        'index' => $i,
        'isPreviewTarget' => $isPreviewTarget,
        'model' => $droid,
    ])
    @php
    $i++;
    if ($i % 3 === 0) {
       $i = 0;
    }
    @endphp
@empty
    <div class="entity-list__item-wrapper entity-list__item-wrapper--not-found wow fadeInUp" data-wow-delay="100ms">
        <p>{{ __('Nothing found') }}</p>
    </div>
@endforelse

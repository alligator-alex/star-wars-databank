@php
use App\Modules\Vehicle\Common\Models\Vehicle;
use Illuminate\Support\Collection;

/**
 * @var Collection<int, Vehicle> $vehicles
 */

$previewTargetId = (int) request()->get('preview-target');

$i = 0;
@endphp
@forelse($vehicles as $vehicle)
    @php
    $isPreviewTarget = ($previewTargetId === $vehicle->id);
    @endphp
    @include('public.vehicles.partials.item-content', [
        'index' => $i,
        'isPreviewTarget' => $isPreviewTarget,
        'model' => $vehicle,
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

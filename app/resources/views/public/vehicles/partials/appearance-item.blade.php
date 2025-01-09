@php
use App\Modules\Databank\Public\Enums\VehicleRouteName;

/**
 * @var App\Modules\Databank\Common\Models\Media $appearance
 */
@endphp
<a href="{{ route(VehicleRouteName::LIST, ['media[]' => $appearance->slug], false) }}"
   class="appearances__item">
    <div class="appearances__poster wow fadeInUp">
        <picture>
            @if ($appearance->poster)
                <img src="{{ $appearance->poster->medium_url }}"
                     alt="{{ $appearance->nameWithReleaseYear() }}"
                     loading="lazy">
            @else
                @php
                    $staticSuffixes = [
                        '',
                        '-fv',
                        '-rev',
                        '-rev-fv',
                    ];
                @endphp
                <img src="/images/static{{ $staticSuffixes[array_rand($staticSuffixes)] }}.gif"
                     alt="{{ $appearance->nameWithReleaseYear() }}"
                     loading="lazy">
            @endif
        </picture>
    </div>
    <div class="appearances__name wow fadeInUp" data-wow-delay="100ms">
        <span>{{ $appearance->name }}</span>
        @if ($appearance->release_date)
            <span class="appearances__release-date">({{ $appearance->releaseYear() }})</span>
        @endif
    </div>
</a>

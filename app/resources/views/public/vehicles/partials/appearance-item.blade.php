@php
use App\Modules\Databank\Public\Enums\VehicleRouteName;

/**
 * @var App\Modules\Databank\Common\Models\Media $appearance
 */
@endphp
<a href="{{ route(VehicleRouteName::LIST, ['media[]' => $appearance->slug], false) }}"
   class="appearances__item wow fadeInUp">
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
    <div class="appearances__info">
        <div class="appearances__name">{{ $appearance->name }}</div>
        <div class="appearances__meta">
            <div class="appearances__type">{{ $appearance->type->nameForHumans() }}</div>
            @if ($appearance->release_date)
                <div class="appearances__release-date">{{ $appearance->release_date->format('M j Y') }}</div>
            @endif
        </div>
    </div>
</a>

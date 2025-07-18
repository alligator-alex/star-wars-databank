@php
use App\Modules\Databank\Public\Enums\DatabankRouteName;
use App\Modules\Databank\Public\Enums\ExploreRootType;
use App\Modules\Databank\Public\Helpers\ViewHelper;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;

/**
 * @var BackedEnum $routeName
 * @var Media $media
 */

$routeParams = match ($routeName) {
    DatabankRouteName::EXPLORE => [
        'type' => ExploreRootType::MEDIA->value,
        'slug' => $media->slug,
    ],
    VehicleRouteName::INDEX, DroidRouteName::INDEX => [
        'media[]' => $media->slug,
    ],
}
@endphp
<a href="{{ route($routeName, $routeParams, false) }}" class="appearances__item">
    <picture>
        @if ($media->poster)
            <img src="{{ $media->poster->medium_url }}"
                 alt="{{ $media->nameWithReleaseYear() }}">
        @else
            <img src="/images/static{{ ViewHelper::imagePlaceholderRandomSuffix() }}.gif"
                 alt="{{ $media->nameWithReleaseYear() }}"
                 loading="lazy">
        @endif
    </picture>
    <div class="appearances__info">
        <div class="appearances__name">{{ $media->name }}</div>
        <div class="appearances__meta">
            <div class="appearances__type">{{ $media->type->nameForHumans() }}</div>
            @if ($media->release_date)
                <div class="appearances__release-date">{{ $media->release_date->format('M j Y') }}</div>
            @endif
        </div>
    </div>
</a>

@php
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Public\Enums\VehicleRouteName;
use \App\Modules\Databank\Common\Enums\VehicleType;
use Illuminate\Support\Collection;

/**
 * @var Collection<Vehicle> $vehicles
 */

$i = 0;
@endphp
@forelse($vehicles as $vehicle)
    @php
    $isPreviewTarget = ((int) request()->get('preview-target') === $vehicle->id);
    $listPageSettings = $vehicle->getPageSettings()->getForList();
    @endphp
    <div class="vehicle-list__item-wrapper wow fadeInUp" data-wow-delay="{{ (($i + 1) * 100) }}ms">
        <a href="{{ route(VehicleRouteName::ONE, $vehicle->slug, false) }}"
           class="vehicle-list__item @if ($listPageSettings->isCardLarge()) vehicle-list__item--large @endif"
           @if ($isPreviewTarget) data-preview-target="true" @endif>
            @php
            $imgStyles = [];

            if ($listPageSettings->isImageScaled() && ($listPageSettings->getImageScale() !== 1.0)) {
                $scalePct = ceil($listPageSettings->getImageScale() * 100);

                $imgStyles[] = 'height: ' . $scalePct . '%';
                $imgStyles[] = 'width: ' . $scalePct . '%';
            }

            if ($listPageSettings->isImageScaled()) {
                $defaultOffset = -50;

                $offsetX = $defaultOffset - $listPageSettings->getImageOffsetX();
                $offsetY = $defaultOffset - $listPageSettings->getImageOffsetY();

                $imgStyles[] = 'transform: translate(' . $offsetX . '%, ' . $offsetY . '%)';
            } else {
                $multiplier = -5;

                $offsetX = $multiplier * $listPageSettings->getImageOffsetX();
                $offsetY = $multiplier * $listPageSettings->getImageOffsetY();

                $imgStyles[] = 'object-position: calc(50% + ' . $offsetX . 'px) calc(50% + ' . $offsetY . 'px)';
            }
            @endphp
            <div
                class="vehicle-list__image @if ($listPageSettings->isImageCovered()) vehicle-list__image--covered @endif">
                @if ($vehicle->factions->isNotEmpty())
                    <div class="vehicle-list__factions">
                        @foreach ($vehicle->factions->sortByDesc('main', SORT_NATURAL) as $faction)
                            <div
                                class="vehicle-list__faction-emblem faction-emblem faction-emblem--{{ $faction->slug }}"
                                title="{{ __('Used by the') }} {{ $faction->name }}">
                                <svg>
                                    <use xlink:href="#emblem-{{ $faction->slug }}"></use>
                                </svg>
                            </div>
                        @endforeach
                    </div>
                @endif
                <picture>
                    @if ($vehicle->image)
                        <img src="{{ $vehicle->image->medium_url }}"
                             loading="lazy"
                             alt="{{ $vehicle->name }}"
                             style="{{ implode(';', $imgStyles) }}">
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
                             class="is-dummy"
                             loading="lazy"
                             alt="{{ $vehicle->name }}">
                    @endif
                </picture>
            </div>
            <div class="vehicle-list__info">
                <div class="vehicle-list__name" title="{{ $vehicle->name }}">{{ $vehicle->name }}</div>

                <div class="rogue-links">
                    <span class="rogue-links__item rogue-links__item--category" title="{{ __('Category') }}">
                        <span class="rogue-links__icon rogue-icon"><noindex>s</noindex></span>
                        <span class="rogue-links__value">{{ $vehicle->category->nameForHumans() }}</span>
                    </span>

                    @if ($vehicle->type && ($vehicle->type !== VehicleType::OTHER))
                        <span class="rogue-links__item rogue-links__item--type" title="{{ __('Type') }}">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>h</noindex></span>
                            </span>
                            <span class="rogue-links__value">{{ $vehicle->type->nameForHumans() }}</span>
                        </span>
                    @endif

                    @if ($vehicle->line)
                        <span class="rogue-links__item rogue-links__item--line" title="{{ __('Line') }}">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>f4</noindex></span>
                            </span>
                            <span class="rogue-links__value">{{ $vehicle->line->name }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </a>
    </div>
    @php
    $i++;
    if ($i % 3 === 0) {
       $i = 0;
    }
    @endphp
@empty
    <div class="vehicle-list__item-wrapper vehicle-list__item-wrapper--not-found wow fadeInUp" data-wow-delay="100ms">
        <p>{{ __('Nothing found') }}</p>
    </div>
@endforelse

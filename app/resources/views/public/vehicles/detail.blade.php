@php
use App\Modules\Core\Public\Components\Breadcrumbs;
use App\Modules\Databank\Common\DTOs\VehiclePageSettings\DetailPagePageSettings;
use App\Modules\Databank\Public\Enums\VehicleRouteName;
use Illuminate\Support\Str;

/**
 * @var App\Modules\Databank\Common\Models\Vehicle $vehicle
 */

$pageSettings = $vehicle->getPageSettings()->getForDetail();
@endphp

@extends('public.layouts.app')

@section('title', $vehicle->name . ' — ' . config('app.name'))
@section('description', Str::limit(str_replace('&nbsp;', ' ', strip_tags($vehicle->description)), 250))
@section('page-wrapper-class', 'vehicle-detail')

@section('copyright-title', $vehicle->name)
@section('copyright-external-url', $vehicle->external_url)

@php
Breadcrumbs::add(__('Vehicles'), VehicleRouteName::LIST->value);
Breadcrumbs::add($vehicle->name, VehicleRouteName::ONE->value, $vehicle->slug);
@endphp

@section('content')
    <section class="vehicle-detail__main-info container">
        <div class="page-title">
            <div class="vehicle-detail__title wow fadeInUp">
                @if ($vehicle->mainFaction)
                    <a href="{{ route(VehicleRouteName::LIST, ['faction[]' => $vehicle->mainFaction->slug], false) }}"
                       class="vehicle-detail__faction"
                       title="{{ __('Mainly used by the') }} {{ $vehicle->mainFaction->name }}">
                        <div class=" faction-emblem faction-emblem--{{ $vehicle->mainFaction->slug }}">
                            <svg>
                                <use xlink:href="#emblem-{{ $vehicle->mainFaction->slug }}"></use>
                            </svg>
                        </div>
                    </a>
                @endif
                <h1>{{ $vehicle->name }}</h1>
            </div>

            <noindex>
                <p class="aurebesh wow fadeInUp" data-wow-delay="100ms">{{ $vehicle->name }}</p>
            </noindex>
        </div>

        @if ($vehicle->image)
            <picture class="vehicle-detail__image wow fadeInRight"
                     @if ($pageSettings->getImageOffsetX() !== DetailPagePageSettings::IMAGE_OFFSET_X_DEFAULT)
                         style="left: {{ $pageSettings->getImageOffsetX() }}%"
                     @endif>
                @php
                $imageStyle = '';
                if ($pageSettings->getImageOffsetY() !== DetailPagePageSettings::IMAGE_OFFSET_Y_DEFAULT) {
                    $imageStyle .= 'top: ' . $pageSettings->getImageOffsetY() . '%;';
                }

                if ($pageSettings->getImageMaxHeight() !== DetailPagePageSettings::IMAGE_MAX_HEIGHT_DEFAULT) {
                    $imageStyle .= 'max-height: ' . $pageSettings->getImageMaxHeight() . 'vh;';
                }
                @endphp
                <img src="{{ $vehicle->image->url }}"
                     alt="{{ $vehicle->name }}"
                     loading="lazy"
                     @if ($imageStyle)
                         style="{{ $imageStyle }}"
                    @endif>
            </picture>
        @endif

        <div class="vehicle-detail__info wow fadeInUp" data-wow-delay="200ms">
            @if ($vehicle->otherFactions->isNotEmpty())
                <div class="vehicle-detail__data vehicle-detail__data--other-factions">
                    <h3>{{ __('Also used by') }}:</h3>
                    <ul class="factions">
                        @foreach ($vehicle->otherFactions as $i => $faction)
                            <li class="vehicle-detail__other-factions-item wow fadeInRight"
                                data-wow-delay="{{ ($i * 100) + 100 }}ms">
                                <a href="{{ route(VehicleRouteName::LIST, ['faction[]' => $faction->slug], false) }}"
                                   class="vehicle-detail__faction vehicle-detail__faction--other"
                                   title="{{ $faction->name }}">
                                    <div class="faction-emblem faction-emblem--{{ $faction->slug }}">
                                        <svg>
                                            <use xlink:href="#emblem-{{ $faction->slug }}"></use>
                                        </svg>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="vehicle-detail__description wow fadeInUp">
                {!! $vehicle->description !!}
            </div>

            <div class="vehicle-detail__meta">
                <div class="rogue-links">
                    <span class="rogue-links__item rogue-links__item--category wow fadeInRight" data-wow-delay="300ms">
                        <span class="rogue-links__label">
                            <span class="rogue-links__icon rogue-icon"><noindex>s</noindex></span> {{ __('Category') }}:
                        </span>
                        <span class="rogue-links__value">
                            <a href="{{ route(VehicleRouteName::LIST, ['category[]' => Str::slug($vehicle->category->nameForHumans())], false) }}"
                               class="rogue-links__link">{{ $vehicle->category->nameForHumans() }}</a>
                        </span>
                    </span>

                    @if ($vehicle->type)
                        <span class="rogue-links__item rogue-links__item--type wow fadeInRight" data-wow-delay="400ms">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>h</noindex></span> {{ __('Type') }}:
                            </span>
                            <span class="rogue-links__value">
                                <a href="{{ route(VehicleRouteName::LIST, ['type[]' => mb_strtolower($vehicle->type->name)], false) }}"
                                   class="rogue-links__link">{{ $vehicle->type->nameForHumans() }}</a>
                            </span>
                        </span>
                    @endif

                    @if ($vehicle->line)
                        <span class="rogue-links__item rogue-links__item--line wow fadeInRight" data-wow-delay="500ms">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>f4</noindex></span> {{ __('Line') }}:
                            </span>
                            <span class="rogue-links__value">
                                <a href="{{ route(VehicleRouteName::LIST, ['line[]' => Str::slug($vehicle->line->name)], false) }}"
                                   class="rogue-links__link">{{ $vehicle->line->name }}</a>
                            </span>
                        </span>
                    @endif

                    @if ($vehicle->manufacturers->isNotEmpty())
                        <span class="rogue-links__item wow fadeInRight" data-wow-delay="600ms">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>r1</noindex></span> {{ __('Manufacturer') }}:
                            </span>
                            @foreach ($vehicle->manufacturers as $i => $manufacturer)
                                <span class="rogue-links__value">
                                    <a href="{{ route(VehicleRouteName::LIST, ['manufacturer[]=' . $manufacturer->slug], false) }}"
                                       class="rogue-links__link">{{ $manufacturer->name }}</a>
                                </span>
                                @if ($i < ($vehicle->manufacturers->count() - 1))
                                    <span class="rogue-links__divider">/</span>
                                @endif
                            @endforeach
                        </span>
                    @endif
                </div>
            </div>

            @php
            $techSpecs = $vehicle->getTechnicalSpecifications()->getItems();
            @endphp
            @if (!empty($techSpecs))
                <div class="vehicle-detail__data">
                    <h2 class="wow fadeInUp">{{ __('Technical Specifications') }}:</h2>
                    <ul class="vehicle-detail__list">
                        @php
                        $i = 0;
                        @endphp
                        @foreach ($techSpecs as $name => $value)
                            <li class="wow fadeInRight" data-wow-delay="{{ ($i * 100) + 100 }}ms">{{ $name }}: <strong>{{ $value }}</strong></li>
                            @php
                            $i++;
                            @endphp
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($vehicle->appearances->isNotEmpty())
                <div class="vehicle-detail__data appearances">
                    <h2 class="wow fadeInUp">{{ __('Appeared in') }}:</h2>
                    <ul class="appearances__list js-appearances-slider">
                        @foreach ($vehicle->appearances as $appearance)
                            <li class="appearances__wrapper">
                                @include('public.vehicles.partials.appearance-item', ['appearance' => $appearance])
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="vehicle-detail__misc">
                <a href="{{ $vehicle->external_url }}" target="_blank" class="pretty-link wow fadeInUp">
                    <span class="pretty-link__icon rogue-icon">f</span>
                    <span>{{ __('Explore more on Wookieepedia') }}</span>
                </a>
            </div>
        </div>
    </section>
@endsection

@php
use App\Modules\Core\Public\Components\Breadcrumbs;
use App\Modules\Databank\Common\DTOs\PageSettings\DetailPagePageSettings;
use App\Modules\Databank\Public\Helpers\ViewHelper;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;
use Illuminate\Support\Str;

/**
 * @var Vehicle $vehicle
 */

$settings = $vehicle->getPageSettings()->getForDetail();

Breadcrumbs::add(__('Vehicles'), VehicleRouteName::INDEX->value);
Breadcrumbs::add($vehicle->name, VehicleRouteName::DETAIL->value, $vehicle->slug);
@endphp

@extends('public.layouts.app')

@section('title', $vehicle->name . ' — ' . config('app.name'))
@section('description', Str::limit(str_replace('&nbsp;', ' ', strip_tags($vehicle->description)), 250))
@section('page-wrapper-class', 'entity-detail')

@section('copyright-title', $vehicle->name)
@section('copyright-external-url', $vehicle->external_url)

@section('content')
    <section class="entity-detail__main-info container">
        <div class="page-title">
            <div class="entity-detail__title wow fadeInUp">
                @if ($vehicle->mainFaction)
                    <a href="{{ route(VehicleRouteName::INDEX, ['factions[]' => $vehicle->mainFaction->slug], false) }}"
                       class="entity-detail__faction"
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
            <picture class="entity-detail__image wow fadeInRight"
                     @if ($settings->getImageOffsetX() !== DetailPagePageSettings::IMAGE_OFFSET_X_DEFAULT)
                         style="left: {{ $settings->getImageOffsetX() }}%"
                @endif>
                <img src="{{ $vehicle->image->url }}"
                     alt="{{ $vehicle->name }}"
                     loading="lazy"
                     style="{{ ViewHelper::detailPageSettingsImageStyle($settings) }}">
            </picture>
        @endif

        <div class="entity-detail__info wow fadeInUp" data-wow-delay="200ms">
            @if ($vehicle->otherFactions->isNotEmpty())
                <div class="entity-detail__data entity-detail__data--other-factions">
                    <h3>{{ __('Also used by') }}:</h3>
                    <ul class="factions">
                        @foreach ($vehicle->otherFactions as $i => $faction)
                            <li class="entity-detail__other-factions-item wow fadeInRight"
                                data-wow-delay="{{ ($i * 100) + 100 }}ms">
                                <a href="{{ route(VehicleRouteName::INDEX, ['factions[]' => $faction->slug], false) }}"
                                   class="entity-detail__faction entity-detail__faction--other"
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

            <div class="entity-detail__description wow fadeInUp">
                {!! $vehicle->description !!}
            </div>

            <div class="entity-detail__meta">
                <div class="rogue-links">
                    <span class="rogue-links__item rogue-links__item--category wow fadeInRight" data-wow-delay="300ms">
                        <span class="rogue-links__label">
                            <span class="rogue-links__icon rogue-icon"><noindex>s</noindex></span> {{ __('Category') }}:
                        </span>
                        <span class="rogue-links__value">
                            <a href="{{ route(VehicleRouteName::INDEX, ['categories[]' => $vehicle->category->slug], false) }}"
                               class="rogue-links__link">{{ $vehicle->category->name }}</a>
                        </span>
                    </span>

                    @if ($vehicle->type)
                        <span class="rogue-links__item rogue-links__item--type wow fadeInRight" data-wow-delay="400ms">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>h</noindex></span> {{ __('Type') }}:
                            </span>
                            <span class="rogue-links__value">
                                <a href="{{ route(VehicleRouteName::INDEX, ['types[]' => $vehicle->type->slug], false) }}"
                                   class="rogue-links__link">{{ $vehicle->type->name }}</a>
                            </span>
                        </span>
                    @endif

                    @if ($vehicle->line)
                        <span class="rogue-links__item rogue-links__item--line wow fadeInRight" data-wow-delay="500ms">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>f4</noindex></span> {{ __('Line') }}:
                            </span>
                            <span class="rogue-links__value">
                                <a href="{{ route(VehicleRouteName::INDEX, ['lines[]' => $vehicle->line->slug], false) }}"
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
                                    <a href="{{ route(VehicleRouteName::INDEX, ['manufacturers[]=' => $manufacturer->slug], false) }}"
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
            $techSpecs = $vehicle->getTechnicalSpecifications()?->getItems() ?? [];
            @endphp
            @if (!empty($techSpecs))
                <div class="entity-detail__data">
                    <h2 class="wow fadeInUp">{{ __('Technical Specifications') }}:</h2>
                    <ul class="entity-detail__list">
                        @php
                        $i = 0;
                        @endphp
                        @foreach ($techSpecs as $name => $value)
                            <li class="wow fadeInRight" data-wow-delay="{{ ($i * 100) + 100 }}ms">{{ $name }}:
                                <strong>{{ $value }}</strong></li>
                            @php
                            $i++;
                            @endphp
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($vehicle->appearances->isNotEmpty())
                <div class="entity-detail__data appearances">
                    <h2 class="wow fadeInRight">{{ __('Appeared in') }}:</h2>
                    <ul class="appearances__list js-appearances-slider wow fadeInUp">
                        @foreach ($vehicle->appearances as $media)
                            <li class="appearances__wrapper">
                                @include('public.media.partials.item-content', [
                                    'routeName' => VehicleRouteName::INDEX,
                                    'media' => $media,
                                ])
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="entity-detail__misc">
                <a href="{{ $vehicle->external_url }}" target="_blank" class="pretty-link wow fadeInUp">
                    <span class="pretty-link__icon rogue-icon">f</span>
                    <span>{{ __('Explore more on Wookieepedia') }}</span>
                </a>
            </div>
        </div>
    </section>
@endsection

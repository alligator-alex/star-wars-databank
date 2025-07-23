@php
use App\Modules\Databank\Common\DTOs\PageSettings\DetailPagePageSettings;
use App\Modules\Databank\Public\Helpers\ViewHelper;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use Illuminate\Support\Str;

/**
 * @var Droid $droid
 */

$settings = $droid->getPageSettings()->getForDetail();
@endphp

@extends('public.layouts.app')

@section('title', $droid->name . ' — ' . config('app.name'))
@section('description', Str::limit(str_replace('&nbsp;', ' ', strip_tags($droid->description)), 250))
@section('page-wrapper-class', '$droid-detail')

@section('copyright-title', $droid->name)
@section('copyright-external-url', $droid->external_url)

@section('content')
    <section class="entity-detail__main-info container">
        <div class="page-title">
            <div class="entity-detail__title wow fadeInUp">
                @if ($droid->mainFaction)
                    <a href="{{ route(DroidRouteName::INDEX, ['factions[]' => $droid->mainFaction->slug], false) }}"
                       class="entity-detail__faction"
                       title="{{ __('Mainly used by the') }} {{ $droid->mainFaction->name }}">
                        <div class=" faction-emblem faction-emblem--{{ $droid->mainFaction->slug }}">
                            <svg>
                                <use xlink:href="#emblem-{{ $droid->mainFaction->slug }}"></use>
                            </svg>
                        </div>
                    </a>
                @endif
                <h1>{{ $droid->name }}</h1>
            </div>

            <noindex>
                <p class="aurebesh wow fadeInUp" data-wow-delay="100ms">{{ $droid->name }}</p>
            </noindex>
        </div>

        @if ($droid->image)
            <picture class="entity-detail__image wow fadeInRight"
                     @if ($settings->getImageOffsetX() !== DetailPagePageSettings::IMAGE_OFFSET_X_DEFAULT)
                         style="left: {{ $settings->getImageOffsetX() }}%"
                @endif>
                <img src="{{ $droid->image->url }}"
                     alt="{{ $droid->name }}"
                     loading="lazy"
                     style="{{ ViewHelper::detailPageSettingsImageStyle($settings) }}">
            </picture>
        @endif

        <div class="entity-detail__info wow fadeInUp" data-wow-delay="200ms">
            @if ($droid->otherFactions->isNotEmpty())
                <div class="entity-detail__data entity-detail__data--other-factions">
                    <h3>{{ __('Also used by') }}:</h3>
                    <ul class="factions">
                        @foreach ($droid->otherFactions as $i => $faction)
                            <li class="entity-detail__other-factions-item wow fadeInRight"
                                data-wow-delay="{{ ($i * 100) + 100 }}ms">
                                <a href="{{ route(DroidRouteName::INDEX, ['factions[]' => $faction->slug], false) }}"
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
                {!! $droid->description !!}
            </div>

            <div class="entity-detail__meta">
                <div class="rogue-links">
                    @if ($droid->line)
                        <span class="rogue-links__item rogue-links__item--category wow fadeInRight"
                              data-wow-delay="300ms">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>f4</noindex></span> {{ __('Line') }}:
                            </span>
                            <span class="rogue-links__value">
                                <a href="{{ route(DroidRouteName::INDEX, ['lines[]' => $droid->line->slug], false) }}"
                                   class="rogue-links__link">{{ $droid->line->name }}</a>
                            </span>
                        </span>
                    @endif

                    @if ($droid->model)
                        <span class="rogue-links__item rogue-links__item--type wow fadeInRight" data-wow-delay="400ms">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>1</noindex></span> {{ __('Model') }}:
                            </span>
                            <span class="rogue-links__value">
                                <a href="{{ route(DroidRouteName::INDEX, ['models[]' => $droid->model->slug], false) }}"
                                   class="rogue-links__link">{{ $droid->model->name }}</a>
                            </span>
                        </span>
                    @endif

                    @if ($droid->class)
                        <span class="rogue-links__item rogue-links__item--line wow fadeInRight" data-wow-delay="500ms">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>e</noindex></span> {{ __('Class') }}:
                            </span>
                            <span class="rogue-links__value">
                                <a href="{{ route(DroidRouteName::INDEX, ['classes[]' => $droid->class->slug], false) }}"
                                   class="rogue-links__link">{{ $droid->class->name }}</a>
                            </span>
                        </span>
                    @endif

                    @if ($droid->manufacturers->isNotEmpty())
                        <span class="rogue-links__item wow fadeInRight" data-wow-delay="600ms">
                            <span class="rogue-links__label">
                                <span class="rogue-links__icon rogue-icon"><noindex>r1</noindex></span> {{ __('Manufacturer') }}:
                            </span>
                            @foreach ($droid->manufacturers as $i => $manufacturer)
                                <span class="rogue-links__value">
                                    <a href="{{ route(DroidRouteName::INDEX, ['manufacturers[]=' => $manufacturer->slug], false) }}"
                                       class="rogue-links__link">{{ $manufacturer->name }}</a>
                                </span>
                                @if ($i < ($droid->manufacturers->count() - 1))
                                    <span class="rogue-links__divider">/</span>
                                @endif
                            @endforeach
                        </span>
                    @endif
                </div>
            </div>

            @php
            $techSpecs = $droid->getTechnicalSpecifications()?->getItems() ?? [];
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

            @if ($droid->appearances->isNotEmpty())
                <div class="entity-detail__data appearances">
                    <h2 class="wow fadeInRight">{{ __('Appeared in') }}:</h2>
                    <ul class="appearances__list js-appearances-slider wow fadeInUp">
                        @foreach ($droid->appearances as $media)
                            <li class="appearances__wrapper">
                                @include('public.media.partials.item-content', [
                                    'routeName' => DroidRouteName::INDEX,
                                    'appearance' => $media,
                                ])
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="entity-detail__misc">
                <a href="{{ $droid->external_url }}" target="_blank" class="pretty-link wow fadeInUp">
                    <span class="pretty-link__icon rogue-icon">f</span>
                    <span>{{ __('Explore more on Wookieepedia') }}</span>
                </a>
            </div>
        </div>
    </section>
@endsection

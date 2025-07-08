@php
use App\Modules\Databank\Common\Enums\CookieName;
use App\Modules\Databank\Public\Enums\DatabankRouteName;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Media\Common\Enums\MediaType;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;

/**
 * @var Collection<int, Vehicle> $vehicles
 * @var Collection<int, Droid> $droids
 * @var Collection<int, Faction> $factions
 * @var Collection<int, MediaType> $availableMediaTypes
 * @var Collection<int, Media> $media
 */

$skipInto = (Cookie::get(CookieName::SKIP_INTRO->value) === 'Y');
@endphp

@extends('public.layouts.app')

@section('title', config('app.name'))
@section('page-wrapper-class', 'page-wrapper--home-page')

@section('content')
    @if (!$skipInto)
        <div class="intro js-intro">
            <p class="intro__title">A long time ago in a galaxy far,<br>far away....</p>
            <button type="button" class="intro__skip-button js-skip-intro">×</button>
        </div>
    @endif

    <section class="container explore">
        <div class="page-title">
            <h1 class="wow fadeInUp">{{ __('Explore') }}</h1>
            <noindex>
                <p class="aurebesh wow fadeInUp" data-wow-delay="100ms">{{ __('Explore') }}</p>
            </noindex>
        </div>

        <div class="entity-explorer js-explorer">
            <a href="{{ route(name: VehicleRouteName::INDEX, absolute: false) }}"
               class="entity-explorer__item js-explorer-item wow fadeInUp" data-wow-delay="200ms">
                @foreach ($vehicles as $i => $vehicle)
                    <div class="entity-explorer__card js-explorer-card @if ($i === 0) is-active @endif">
                        <picture>
                            <img src="{{ $vehicle->image->medium_url }}"
                                 alt="{{ $vehicle->name }}">
                        </picture>
                    </div>
                @endforeach
                <div class="entity-explorer__overlay">
                    <h2 class="entity-explorer__title">{{ __('Vehicles') }}</h2>
                </div>
            </a>

            <a href="{{ route(name: DroidRouteName::INDEX, absolute: false) }}"
               class="entity-explorer__item js-explorer-item wow fadeInUp" data-wow-delay="200ms">
                @foreach ($droids as $i => $droid)
                    <div class="entity-explorer__card js-explorer-card @if ($i === 0) is-active @endif">
                        <picture>
                            <img src="{{ $droid->image->medium_url }}"
                                 alt="{{ $droid->name }}">
                        </picture>
                    </div>
                @endforeach
                <div class="entity-explorer__overlay">
                    <h2 class="entity-explorer__title">{{ __('Droids') }}</h2>
                </div>
            </a>
        </div>
    </section>

    <section class="container">
        <div class="heading-wrapper">
            <h2 class="wow fadeInUp" data-wow-delay="400ms">
                <span class="rogue-icon"><noindex>a</noindex></span>
                <span>{{ __('By Faction') }}</span>
            </h2>
        </div>

        <div class="factions-selector wow fadeIn" data-wow-delay="500ms">
            @foreach ($factions as $i => $faction)
                <a href="{{ route(DatabankRouteName::EXPLORE, [
                    'type' => 'faction',
                    'slug' => $faction->slug,
                ], false) }}" class="factions-selector__item wow fadeInUp" data-wow-delay="{{ (($i + 1) * 100) }}ms">
                    <div class="factions-selector__emblem faction-emblem faction-emblem--{{ $faction->slug }}">
                        <svg>
                            <use xlink:href="#emblem-{{ $faction->slug }}"></use>
                        </svg>
                    </div>
                    <div class="factions-selector__name">{!! $faction->formattedName() !!}</div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="container">
        <div class="heading-wrapper">
            <h2 class="wow fadeInUp" data-wow-delay="600ms">
                <span class="rogue-icon"><noindex>5</noindex></span>
                <span>{{ __('By Media') }}</span>
            </h2>

            <div class="switcher">
                <label class=" wow fadeInRight" data-wow-delay="650ms">
                    <input type="radio"
                           name="mediaType"
                           value="0"
                           checked
                           class="js-media-type-switch">
                    <span class="switcher__name">{{ __('All') }}</span>
                </label>
                @foreach ($availableMediaTypes as $i => $mediaType)
                    <label class=" wow fadeInRight" data-wow-delay="{{ (($i + 1) * 50 + 650) }}ms">
                        <input type="radio"
                               name="mediaType"
                               value="{{ $mediaType->value }}"
                               class="js-media-type-switch">
                        <span class="switcher__name">{{ $mediaType->nameForHumans() }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="appearances wow fadeInUp" data-wow-delay="700ms">
            @php
            $i = 0;
            @endphp
            <ul class="appearances__list">
                @foreach ($media as $mediaItem)
                    <li class="appearances__wrapper wow fadeInUp js-appearance"
                        data-media-type="{{ $mediaItem->type->value }}"
                        data-wow-delay="{{ (($i + 1) * 50) }}ms">
                        @include('public.media.partials.item-content', [
                            'routeName' => DatabankRouteName::EXPLORE,
                            'media' => $mediaItem,
                        ])
                    </li>
                    @php
                    $i++;
                    if ($i % 5 === 0) {
                       $i = 0;
                    }
                    @endphp
                @endforeach
            </ul>
        </div>
    </section>
@endsection

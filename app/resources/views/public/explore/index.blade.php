@php
use App\Modules\Core\Public\Components\Breadcrumbs;
use App\Modules\Databank\Public\Enums\DatabankRouteName;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;
use Illuminate\Support\Collection;

/**
 * @var string $type
 * @var Faction|Media $root
 * @var Collection<int, Vehicle|Droid> $models
 * @var Collection<int, Vehicle> $randomVehicles
 * @var Collection<int, Droid> $randomDroids
 */

$i = 0;

$titleSuffix = match ($root::class) {
    Faction::class => __('used by the'),
    Media::class => __('appeared in the'),
};

$routeParams = match ($root::class) {
    Faction::class => [
        'factions[]' => $root->slug,
    ],
    Media::class => [
        'media[]' => $root->slug,
    ],
};

Breadcrumbs::add(__('Explore'), DatabankRouteName::HOME->value);
Breadcrumbs::add(__(class_basename($root)), DatabankRouteName::EXPLORE->value, ['type' => $type, 'slug' => $root->slug]);
Breadcrumbs::add($root->name, DatabankRouteName::EXPLORE->value, ['type' => $type, 'slug' => $root->slug]);
@endphp

@extends('public.layouts.app')

@section('title', __('Explore') . ' ' . $titleSuffix . ' ' . $root->name . ' — ' . config('app.name'))

@section('content')
    <section class="container">
        <div class="heading-wrapper heading-wrapper--main">
            <div class="page-title">
                <h1 class="wow fadeInUp">{{ $root->name }}</h1>
                <noindex>
                    <p class="aurebesh wow fadeInUp" data-wow-delay="100ms">{{ $root->name }}</p>
                </noindex>
            </div>
        </div>
    </section>

    <div class="loader-wrapper">
        <section class="container">
            <div class="entity-list js-index-content">
                @forelse ($models as $model)
                    @if ($model instanceof Vehicle)
                        @include('public.vehicles.partials.item-content', [
                            'index' => $i,
                            'model' => $model,
                        ])
                    @elseif ($model instanceof Droid)
                        @include('public.droids.partials.item-content', [
                            'index' => $i,
                            'model' => $model,
                        ])
                    @endif
                    @php
                    $i++;
                    if ($i % 3 === 0) {
                       $i = 0;
                    }
                    @endphp
                @empty
                    <div class="entity-list__item-wrapper entity-list__item-wrapper--not-found wow fadeInUp"
                         data-wow-delay="100ms">
                        <p>{{ __('Nothing found') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="container">
            <div class="entity-explorer js-explorer">
                <a href="{{ route(VehicleRouteName::INDEX, $routeParams, false) }}"
                   class="entity-explorer__item js-explorer-item wow fadeInUp" data-wow-delay="200ms">
                    @foreach ($randomVehicles as $i => $vehicle)
                        <div class="entity-explorer__card js-explorer-card @if ($i === 0) is-active @endif">
                            <picture>
                                <img src="{{ $vehicle->image->medium_url }}"
                                     alt="{{ $vehicle->name }}">
                            </picture>
                        </div>
                    @endforeach
                    <div class="entity-explorer__overlay">
                        <h2 class="entity-explorer__title">{{ __('Other vehicles') }}</h2>
                    </div>
                </a>

                <a href="{{ route(DroidRouteName::INDEX, $routeParams, false) }}"
                   class="entity-explorer__item js-explorer-item wow fadeInUp" data-wow-delay="200ms">
                    @foreach ($randomDroids as $i => $droid)
                        <div class="entity-explorer__card js-explorer-card @if ($i === 0) is-active @endif">
                            <picture>
                                <img src="{{ $droid->image->medium_url }}"
                                     alt="{{ $droid->name }}">
                            </picture>
                        </div>
                    @endforeach
                    <div class="entity-explorer__overlay">
                        <h2 class="entity-explorer__title">{{ __('Other droids') }}</h2>
                    </div>
                </a>
            </div>
        </section>
    </div>
@endsection

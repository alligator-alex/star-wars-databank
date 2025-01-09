@php
use App\Modules\Core\Public\Components\Breadcrumbs;
use App\Modules\Databank\Common\Models\Vehicle;
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Public\Enums\VehicleRouteName;
use App\Modules\Databank\Common\Enums\VehicleType;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

/**
 * @var array<string, string[]> $appliedFilters
 * @var Collection<Vehicle> $vehicles
 * @var array<int, string> $factions
 * @var array<int, string> $manufacturers
 * @var array<string, array<int, string>> $media
 * @var array<int, string> $lines
 * @var VehicleCategory[] $categories
 * @var VehicleType[] $types
 * @var Htmlable $pagination
 */

Breadcrumbs::add(__('Vehicles'), VehicleRouteName::LIST->value);
@endphp

@extends('public.layouts.app')

@section('title', 'Vehicles — ' . config('app.name'))
@section('page-wrapper-class', 'vehicle-list-page')

@section('content')
    <section class="container">
        <div class="page-title">
            <h1 class="wow fadeInUp">Vehicles</h1>
            <noindex>
                <p class="aurebesh wow fadeInUp" data-wow-delay="100ms">Vehicles</p>
            </noindex>
        </div>
    </section>

    @include('public.vehicles.list.filter', [
        'appliedFilters' => $appliedFilters,
        'factions' => $factions,
        'manufacturers' => $manufacturers,
        'media' => $media,
        'lines' => $lines,
        'categories' => $categories,
        'types' => $types,
    ])

    <div class="loader-wrapper">
        @include('public.components.loader')

        <section class="container">
            <div class="vehicle-list js-list-content">
                @include('public.vehicles.list.content', [
                    'vehicles' => $vehicles,
                ])
            </div>
        </section>

        @if (!request()->has('preview-target'))
            <div class="js-pagination-content">
                {!! $pagination !!}
            </div>
        @endif
    </div>
@endsection

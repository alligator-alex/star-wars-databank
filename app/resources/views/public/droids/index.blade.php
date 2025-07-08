@php
use App\Modules\Core\Public\Components\Breadcrumbs;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Public\Enums\DroidRouteName;
use App\Modules\Handbook\Common\Models\HandbookValue;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

/**
 * @var array<string, string[]> $appliedFilters
 * @var int $appliedFiltersCount
 * @var Collection<int, Droid> $droids
 * @var array<int, string> $factions
 * @var array<int, string> $manufacturers
 * @var array<string, array<int, string>> $media
 * @var HandbookValue[] $lines
 * @var HandbookValue[] $models
 * @var HandbookValue[] $classes
 * @var Htmlable $pagination
 */

Breadcrumbs::add(__('Droids'), DroidRouteName::INDEX->value);
@endphp

@extends('public.layouts.app')

@section('title', __('Droids') . ' — ' . config('app.name'))
@section('page-wrapper-class', 'droid-index-page')

@section('content')
    <section class="container">
        <div class="heading-wrapper heading-wrapper--main">
            <div class="page-title">
                <h1 class="wow fadeInUp">{{ __('Droids') }}</h1>
                <noindex>
                    <p class="aurebesh wow fadeInUp" data-wow-delay="100ms">{{ __('Droids') }}</p>
                </noindex>
            </div>

            <div class="filter-toggle">
                <div class="entity-filter__title">
                    <button
                        class="entity-filter__toggle wow fadeInUp js-filter-toggle @if ($appliedFiltersCount > 0) is-active @endif"
                        data-wow-delay="150ms">
                        <span class="burger-menu-icon">
                            <span class="burger-menu-icon__item"></span>
                            <span class="burger-menu-icon__item"></span>
                            <span class="burger-menu-icon__item"></span>
                        </span>
                        <span class="entity-filter__toggle-label js-filter-toggle-label"
                              data-show-label="{{ __('Show filters') }}"
                              data-hide-label="{{ __('Hide filters') }}"
                              data-applied-filters-count="{{ $appliedFiltersCount }}"></span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    @include('public.droids.partials.filter', [
        'appliedFilters' => $appliedFilters,
        'factions' => $factions,
        'manufacturers' => $manufacturers,
        'media' => $media,
        'lines' => $lines,
        'models' => $models,
        'classes' => $classes,
    ])

    <div class="loader-wrapper">
        @include('public.common.loader')

        <section class="container">
            <div class="entity-list js-index-content">
                @include('public.droids.partials.index-content', [
                    'droids' => $droids,
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

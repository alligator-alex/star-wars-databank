@php
use App\Modules\Vehicle\Public\Enums\VehicleRouteName;

/**
 * @var array<string, string[]> $appliedFilters
 * @var int $appliedFiltersCount
 * @var array<int, string> $factions
 * @var array<int, string> $manufacturers
 * @var array<string, array<int, string>> $media
 * @var array<int, string> $categories
 * @var array<int, string> $types
 * @var array<int, string> $lines
 */
@endphp
<section class="container wow fadeInUp" data-wow-delay="150ms">
    <form class="entity-filter js-entity-filter @if ($appliedFiltersCount > 0) is-active @endif"
          action="{{ route(name: VehicleRouteName::INDEX, absolute: false) }}">
        <div class="entity-filter__controls">
            <div class="entity-filter__control">
                <select multiple name="factions[]"
                        class="entity-filter__input entity-filter__input--faction js-filter-input"
                        data-placeholder="{{ __('Faction') }}">
                    @foreach ($factions as $slug => $name)
                        <option value="{{ $slug }}"
                                @if (in_array($slug, $appliedFilters['factions'], true)) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="entity-filter__control">
                <select multiple name="media[]"
                        class="entity-filter__input entity-filter__input--media js-filter-input"
                        data-placeholder="{{ __('Media') }}">
                    @foreach ($media as $groupName => $items)
                        <optgroup label="{{ $groupName }}:">
                            @foreach ($items as $slug => $name)
                                <option value="{{ $slug }}"
                                        @if (in_array($slug, $appliedFilters['media'], true)) selected @endif>{{ $name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="entity-filter__control">
                <select multiple name="manufacturers[]"
                        class="entity-filter__input entity-filter__input--manufacturer js-filter-input"
                        data-placeholder="{{ __('Manufacturer') }}">
                    @foreach ($manufacturers as $slug => $name)
                        <option value="{{ $slug }}"
                                @if (in_array($slug, $appliedFilters['manufacturers'], true)) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="entity-filter__control">
                <select multiple name="categories[]"
                        class="entity-filter__input entity-filter__input--category js-filter-input"
                        data-placeholder="{{ __('Category') }}">
                    @foreach ($categories as $slug => $name)
                        <option value="{{ $slug }}"
                                @if (in_array($slug, $appliedFilters['categories'], true)) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="entity-filter__control">
                <select multiple name="types[]"
                        class="entity-filter__input entity-filter__input--type js-filter-input"
                        data-placeholder="{{ __('Type') }}">
                    @foreach ($types as $slug => $name)
                        <option value="{{ $slug }}"
                                @if (in_array($slug, $appliedFilters['types'], true)) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="entity-filter__control">
                <select multiple name="lines[]"
                        class="entity-filter__input entity-filter__input--line js-filter-input"
                        data-placeholder="{{ __('Line') }}">
                    @foreach ($lines as $slug => $name)
                        <option value="{{ $slug }}"
                                @if (in_array($slug, $appliedFilters['lines'], true)) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
</section>

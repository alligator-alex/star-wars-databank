@php
use App\Modules\Databank\Common\Enums\VehicleCategory;
use App\Modules\Databank\Public\Enums\VehicleRouteName;
use App\Modules\Databank\Common\Enums\VehicleType;
use Illuminate\Support\Str;

/**
 * @var array<string, string[]> $appliedFilters
 * @var array<int, string> $factions
 * @var array<int, string> $manufacturers
 * @var array<string, array<int, string>> $media
 * @var array<int, string> $lines
 * @var VehicleCategory[] $categories
 * @var VehicleType[] $types
 */
@endphp
<section class="container">
    <form class="vehicle-filter js-vehicle-filter" action="{{ route(name: VehicleRouteName::LIST, absolute: false) }}">
        <input id="filter-controls-toggle" type="checkbox" @if (!empty(array_filter($appliedFilters))) checked @endif>
        <div class="vehicle-filter__title">
            <label class="vehicle-filter__toggle wow fadeInUp"
                   for="filter-controls-toggle"
                   data-wow-delay="100ms">
                <span class="burger-menu-icon">
                    <span class="burger-menu-icon__item"></span>
                    <span class="burger-menu-icon__item"></span>
                    <span class="burger-menu-icon__item"></span>
                </span>
                <span class="vehicle-filter__toggle-label js-filter-toggle"
                      data-show-label="{{ __('Show filters') }}"
                      data-hide-label="{{ __('Hide filters') }}"
                      data-applied-filters-count="{{ count(array_filter($appliedFilters)) }}"></span>
            </label>
        </div>

        <div class="vehicle-filter__controls">
            <div class="vehicle-filter__control">
                <select multiple name="faction[]"
                        class="vehicle-filter__input vehicle-filter__input--faction js-filter-input"
                        data-placeholder="{{ __('Faction') }}">
                    @foreach ($factions as $slug => $name)
                        <option value="{{ $slug }}"
                                @if (in_array($slug, $appliedFilters['factions'], true)) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="vehicle-filter__control">
                <select multiple name="media[]"
                        class="vehicle-filter__input vehicle-filter__input--media js-filter-input"
                        data-placeholder="{{ __('Media') }}">
                    @foreach ($media as $groupName => $items)
                        <optgroup label="{{ $groupName }}">
                            @foreach ($items as $slug => $name)
                                <option value="{{ $slug }}"
                                        @if (in_array($slug, $appliedFilters['media'], true)) selected @endif>{{ $name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="vehicle-filter__control">
                <select multiple name="manufacturer[]"
                        class="vehicle-filter__input vehicle-filter__input--manufacturer js-filter-input"
                        data-placeholder="{{ __('Manufacturer') }}">
                    @foreach ($manufacturers as $slug => $name)
                        <option value="{{ $slug }}"
                                @if (in_array($slug, $appliedFilters['manufacturers'], true)) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="vehicle-filter__control">
                <select multiple name="category[]"
                        class="vehicle-filter__input vehicle-filter__input--category js-filter-input"
                        data-placeholder="{{ __('Category') }}">
                    @foreach ($categories as $category)
                        @php
                        $slug = Str::slug($category->nameForHumans());
                        @endphp
                        <option value="{{ $slug }}"
                                @if (in_array($slug, $appliedFilters['categories'], true)) selected @endif>{{ $category->nameForHumans() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="vehicle-filter__control">
                <select multiple name="type[]"
                        class="vehicle-filter__input vehicle-filter__input--type js-filter-input"
                        data-placeholder="{{ __('Type') }}">
                    @foreach ($types as $type)
                        @php
                        $slug = Str::slug($type->nameForHumans());
                        @endphp
                        <option value="{{ $slug }}"
                                @if (in_array($slug, $appliedFilters['types'], true)) selected @endif>{{ $type->nameForHumans() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="vehicle-filter__control">
                <select multiple name="line[]"
                        class="vehicle-filter__input vehicle-filter__input--line js-filter-input"
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

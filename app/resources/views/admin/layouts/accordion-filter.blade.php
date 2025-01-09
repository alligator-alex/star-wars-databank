@php
/**
 * @var Orchid\Filters\Filter[] $filters
 */

$uniqueId = str_replace('.', '', uniqid('filter-', true));

$isAnyFilterApplied = false;
foreach ($filters as $filter) {
    if ($filter->isApply()) {
        $isAnyFilterApplied = true;
        break;
    }
}
@endphp
<div id="accordion-filter" class="accordion mb-3">
    <div class="accordion-heading @if (!$isAnyFilterApplied) collapsed @endif"
         id="heading-{{ $uniqueId }}"
         data-bs-toggle="collapse"
         data-bs-target="#collapse-{{ $uniqueId }}"
         aria-expanded="true"
         aria-controls="collapse-{{ $uniqueId }}">
        <h6 class="btn btn-link btn-group-justified pt-2 pb-2 mb-0 pe-0 ps-0 d-flex align-items-center">
            <x-orchid-icon path="bs.chevron-right" class="small me-2"/>
            {{ __('Filters') }}
        </h6>
    </div>

    <div id="collapse-{{ $uniqueId }}"
         class="mt-2 collapse @if ($isAnyFilterApplied) show @endif"
         aria-labelledby="heading-{{ $uniqueId }}">
        <div class="g-0 bg-white rounded mb-3">
            <div class="row align-items-center p-4" data-controller="filter">
                @foreach ($filters as $filter)
                    <div class="col-sm-auto col-md mb-3 align-self-start" style="min-width: 200px;">
                        {!! $filter->render() !!}
                    </div>
                @endforeach
                <div class="col-sm-auto ms-auto text-end">
                    <div class="btn-group" role="group">
                        <button data-action="filter#clear" class="btn btn-outline-danger">
                            <x-orchid-icon class="me-1" path="bs.arrow-repeat"/> {{ __('Reset') }}
                        </button>
                        <button type="submit" form="filters" class="btn btn-outline-success">
                            <x-orchid-icon class="me-1" path="bs.funnel"/> {{ __('Apply') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

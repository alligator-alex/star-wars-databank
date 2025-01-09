@php
/**
 * @var Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
 */
@endphp
@if ($paginator->hasPages() && $paginator->hasMorePages())
    <nav class="pagination pagination--infinite"
        data-current-page="{{ $paginator->currentPage() }}">
        <a rel="next"
           aria-label="{{ __('Next') }}"
           data-page-num="{{ $paginator->currentPage() + 1 }}"
           class="yellow-button wow fadeInUp"
           data-wow-delay="100ms">
            <span class="rogue-icon"><noindex>1</noindex></span>
            <span>{{ __('More') }} ({{ $paginator->total() - $paginator->lastItem() }})</span>
        </a>
    </nav>
@endif

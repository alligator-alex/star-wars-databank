@php
/**
 * @var Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
 * @var string $iconContent
 */
@endphp
@if ($paginator->hasPages() && $paginator->hasMorePages())
    <nav class="pagination pagination--infinite"
        data-current-page="{{ $paginator->currentPage() }}">
        <a href="{{ $paginator->nextPageUrl() }}"
           rel="next"
           aria-label="{{ __('Next') }}"
           data-page-num="{{ $paginator->currentPage() + 1 }}"
           class="button button--yellow wow fadeInUp"
           data-wow-delay="100ms">
            <span class="rogue-icon"><noindex>{{ $iconContent ?? 8 }}</noindex></span>
            <span>{{ __('More') }} ({{ $paginator->total() - $paginator->lastItem() }})</span>
        </a>
    </nav>
@endif

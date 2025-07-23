@if (Breadcrumbs::has())
    <div class="container">
        <div class="breadcrumbs wow fadeInRight">
            @foreach (Breadcrumbs::current() as $crumb)
                @if (!$loop->first)
                    <span class="breadcrumbs__divider wow fadeInRight"
                          data-wow-delay="{{ ($loop->index + 1) * 100 + 100 }}ms">/</span>
                @endif
                <span class="breadcrumbs__item wow fadeInRight"
                      data-wow-delay="{{ ($loop->index + 1) * 100 + 100 }}ms">
                    @if (!$loop->last && $crumb->url())
                        <a class="breadcrumbs__link" href="{{ $crumb->url() }}">{{ $crumb->title() }}</a>
                    @else
                        <span class="breadcrumbs__text">{{ $crumb->title() }}</span>
                    @endif
                </span>
            @endforeach
        </div>
    </div>
@endif
